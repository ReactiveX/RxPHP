<?php

declare(strict_types=1);

namespace Rx\Operator;

use Rx\Disposable\CallbackDisposable;
use Rx\Disposable\CompositeDisposable;
use Rx\Disposable\RefCountDisposable;
use Rx\Disposable\SingleAssignmentDisposable;
use Rx\DisposableInterface;
use Rx\Observable\GroupedObservable;
use Rx\ObservableInterface;
use Rx\ObserverInterface;
use Rx\Subject\Subject;

final class GroupByUntilOperator implements OperatorInterface
{
    public function __construct(
        private readonly null|\Closure $keySelector,
        private null|\Closure          $elementSelector = null,
        private null|\Closure          $durationSelector = null,
        private null|\Closure          $keySerializer = null,
        private array                  $map = []
    ) {
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

        $this->elementSelector  = $elementSelector;
        $this->durationSelector = $durationSelector;
        $this->keySerializer    = $keySerializer;
    }

    public function __invoke(ObservableInterface $observable, ObserverInterface $observer): DisposableInterface
    {
        $groupDisposable    = new CompositeDisposable();
        $refCountDisposable = new RefCountDisposable($groupDisposable);
        $sourceEmits        = true;

        $handleError = function (\Throwable $e) use ($observer, &$sourceEmits): void {
            foreach ($this->map as $w) {
                $w->onError($e);
            }
            $observer->onError($e);
        };

        $subscription = $observable->subscribe(
            function ($element) use ($observer, $handleError, $refCountDisposable, $groupDisposable, &$sourceEmits): void {
                try {
                    $key           = call_user_func($this->keySelector, $element);
                    $serializedKey = call_user_func($this->keySerializer, $key);
                } catch (\Throwable $e) {
                    $handleError($e);
                    return;
                }

                if (!$sourceEmits && !isset($this->map[$serializedKey])) {
                    return;
                }

                if (!isset($this->map[$serializedKey])) {
                    $writer                    = new Subject();
                    $this->map[$serializedKey] = $writer;
                    $group                     = new GroupedObservable($key, $writer, $refCountDisposable);
                    $durationGroup             = new GroupedObservable($key, $writer);

                    try {
                        $duration = call_user_func($this->durationSelector, $durationGroup);
                    } catch (\Throwable $e) {
                        $handleError($e);
                        return;
                    }

                    $observer->onNext($group);

                    $md = new SingleAssignmentDisposable();
                    $groupDisposable->add($md);

                    $durationSubscription = $duration->take(1)->subscribe(
                        null,
                        $handleError,
                        function () use ($groupDisposable, $md, $writer, $serializedKey): void {
                            unset($this->map[$serializedKey]);
                            $writer->onCompleted();

                            $groupDisposable->remove($md);
                        });

                    $md->setDisposable($durationSubscription);
                }

                if (is_callable($this->elementSelector)) {
                    try {
                        $element = call_user_func($this->elementSelector, $element);
                    } catch (\Throwable $e) {
                        $handleError($e);
                        return;
                    }
                }

                $this->map[$serializedKey]->onNext($element);
            },
            $handleError,
            function () use ($observer, &$sourceEmits): void {
                foreach ($this->map as $w) {
                    $w->onCompleted();
                }
                $observer->onCompleted();
            });

        $groupDisposable->add($subscription);

        return new CallbackDisposable(function () use (&$sourceEmits, $refCountDisposable): void {
            $sourceEmits = false;
            $refCountDisposable->dispose();
        });
    }
}
