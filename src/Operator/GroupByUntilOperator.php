<?php

declare(strict_types = 1);

namespace Rx\Operator;

use Rx\Disposable\CallbackDisposable;
use Rx\Disposable\CompositeDisposable;
use Rx\Disposable\RefCountDisposable;
use Rx\Disposable\SingleAssignmentDisposable;
use Rx\DisposableInterface;
use Rx\Observable\GroupedObservable;
use Rx\ObservableInterface;
use Rx\Observer\CallbackObserver;
use Rx\ObserverInterface;
use Rx\Subject\Subject;

final class GroupByUntilOperator implements OperatorInterface
{
    /** @var callable */
    private $keySelector;

    /** @var callable */
    private $elementSelector;

    /** @var callable */
    private $durationSelector;

    /** @var callable */
    private $keySerializer;

    public function __construct(callable $keySelector, callable $elementSelector = null, callable $durationSelector = null, callable $keySerializer = null)
    {

        if (null === $elementSelector) {
            $elementSelector = function ($elem) {
                return $elem;
            };
        }

        if (null === $durationSelector) {
            $durationSelector = function ($x) {
                return $x;
            };
        }

        if (null === $keySerializer) {
            $keySerializer = function ($x) {
                return $x;
            };
        }

        $this->keySelector      = $keySelector;
        $this->elementSelector  = $elementSelector;
        $this->durationSelector = $durationSelector;
        $this->keySerializer    = $keySerializer;
    }

    public function __invoke(ObservableInterface $observable, ObserverInterface $observer): DisposableInterface
    {
        $map                = [];
        $groupDisposable    = new CompositeDisposable();
        $refCountDisposable = new RefCountDisposable($groupDisposable);

        $keySelector      = $this->keySelector;
        $elementSelector  = $this->elementSelector;
        $durationSelector = $this->durationSelector;
        $keySerializer    = $this->keySerializer;
        $sourceEmits      = true;

        $callbackObserver = new CallbackObserver(
            function ($value) use (&$map, $keySelector, $elementSelector, $durationSelector, $observer, $keySerializer, $groupDisposable, $refCountDisposable, &$sourceEmits) {
                try {
                    $key           = $keySelector($value);
                    $serializedKey = $keySerializer($key);
                } catch (\Throwable $e) {
                    foreach ($map as $groupObserver) {
                        $groupObserver->onError($e);
                    }
                    $observer->onError($e);

                    return;
                }

                $fireNewMapEntry = false;

                if (!isset($map[$serializedKey])) {
                    $map[$serializedKey] = new Subject();
                    $fireNewMapEntry     = true;
                }
                $writer = $map[$serializedKey];

                if ($fireNewMapEntry) {
                    $group         = new GroupedObservable($key, $writer, $refCountDisposable);
                    $durationGroup = new GroupedObservable($key, $writer);

                    try {
                        $duration = $durationSelector($durationGroup);
                    } catch (\Throwable $e) {
                        foreach ($map as $groupObserver) {
                            $groupObserver->onError($e);
                        }
                        $observer->onError($e);

                        return;
                    }

                    if ($sourceEmits) {
                        $observer->onNext($group);
                    }
                    $md = new SingleAssignmentDisposable();
                    $groupDisposable->add($md);
                    $expire = function () use (&$map, &$md, $serializedKey, &$writer, &$groupDisposable) {
                        if (isset($map[$serializedKey])) {
                            unset($map[$serializedKey]);
                            $writer->onCompleted();
                        }
                        $groupDisposable->remove($md);
                    };

                    $callbackObserver = new CallbackObserver(
                        function () {
                        },
                        function (\Throwable $exception) use ($map, $observer) {
                            foreach ($map as $writer) {
                                $writer->onError($exception);
                            }

                            $observer->onError($exception);
                        },
                        function () use ($expire) {
                            $expire();
                        }
                    );

                    $subscription = $duration->take(1)->subscribe($callbackObserver);

                    $md->setDisposable($subscription);
                }

                try {
                    $element = $elementSelector($value);
                } catch (\Throwable $exception) {
                    foreach ($map as $writer) {
                        $writer->onError($exception);
                    }

                    $observer->onError($exception);
                    return;
                }
                $writer->onNext($element);
            },
            function (\Throwable $error) use (&$map, $observer) {
                foreach ($map as $writer) {
                    $writer->onError($error);
                }

                $observer->onError($error);
            },
            function () use (&$map, $observer) {
                foreach ($map as $writer) {
                    $writer->onCompleted();
                }

                $observer->onCompleted();
            }
        );

        $subscription = $observable->subscribe($callbackObserver);

        $groupDisposable->add($subscription);

        $sourceDisposable = new CallbackDisposable(function () use ($refCountDisposable, &$sourceEmits) {
            $sourceEmits = false;
            $refCountDisposable->dispose();
        });

        return $sourceDisposable;
    }
}
