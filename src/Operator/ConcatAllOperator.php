<?php

declare(strict_types = 1);

namespace Rx\Operator;

use Rx\Disposable\CompositeDisposable;
use Rx\Disposable\EmptyDisposable;
use Rx\Disposable\SerialDisposable;
use Rx\DisposableInterface;
use Rx\ObservableInterface;
use Rx\Observer\CallbackObserver;
use Rx\ObserverInterface;

final class ConcatAllOperator implements OperatorInterface
{
    /** @var  array */
    private $buffer;

    /** @var CompositeDisposable */
    private $disposable;

    /** @var SerialDisposable */
    private $innerDisposable;

    /** @var bool */
    private $startBuffering;

    /** @var bool */
    private $sourceCompleted;

    /** @var bool */
    private $innerCompleted;

    public function __construct()
    {
        $this->buffer          = [];
        $this->disposable      = new CompositeDisposable();
        $this->innerDisposable = new EmptyDisposable();
        $this->startBuffering  = false;
        $this->sourceCompleted = false;
        $this->innerCompleted  = true;
    }

    public function __invoke(ObservableInterface $observable, ObserverInterface $observer): DisposableInterface
    {
        $subscription = $observable->subscribe(new CallbackObserver(
            function (ObservableInterface $innerObservable) use ($observable, $observer) {
                try {

                    if ($this->startBuffering === true) {
                        $this->buffer[] = $innerObservable;
                        return;
                    }

                    $onCompleted = function () use (&$subscribeToInner, $observer) {

                        $this->disposable->remove($this->innerDisposable);
                        $this->innerDisposable->dispose();

                        $this->innerCompleted = true;

                        $obs = array_shift($this->buffer);

                        if (empty($this->buffer)) {
                            $this->startBuffering = false;
                        }

                        if ($obs) {
                            $subscribeToInner($obs);
                        } elseif ($this->sourceCompleted === true) {
                            $observer->onCompleted();
                        }
                    };

                    $subscribeToInner = function ($observable) use ($observer, &$onCompleted) {
                        $callbackObserver = new CallbackObserver(
                            [$observer, 'onNext'],
                            [$observer, 'onError'],
                            $onCompleted
                        );

                        $this->innerCompleted = false;
                        $this->startBuffering = true;

                        $this->innerDisposable = $observable->subscribe($callbackObserver);
                        $this->disposable->add($this->innerDisposable);
                    };

                    $subscribeToInner($innerObservable);

                } catch (\Throwable $e) {
                    $observer->onError($e);
                }
            },
            [$observer, 'onError'],
            function () use ($observer) {
                $this->sourceCompleted = true;
                if ($this->innerCompleted === true) {
                    $observer->onCompleted();
                }
            }
        ));

        $this->disposable->add($subscription);

        return $this->disposable;
    }
}
