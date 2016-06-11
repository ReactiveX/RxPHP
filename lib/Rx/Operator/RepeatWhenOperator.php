<?php

namespace Rx\Operator;

use Rx\Disposable\CompositeDisposable;
use Rx\Disposable\EmptyDisposable;
use Rx\Disposable\SerialDisposable;
use Rx\Observer\CallbackObserver;
use Rx\ObservableInterface;
use Rx\SchedulerInterface;
use Rx\ObserverInterface;
use Rx\Subject\Subject;

class RepeatWhenOperator implements OperatorInterface
{
    /** @var callable */
    private $notificationHandler;

    /** @var Subject */
    private $completions;

    /** @var Subject */
    private $notifier;

    /** @var CompositeDisposable */
    private $disposable;

    /** @var bool */
    private $repeat;

    /** @var int */
    private $count;

    /** @var bool */
    private $sourceComplete;

    public function __construct(callable $notificationHandler)
    {
        $this->notificationHandler = $notificationHandler;
        $this->completions         = new Subject();
        $this->disposable          = new CompositeDisposable();
        $this->notifier            = new Subject();
        $this->repeat              = true;
        $this->count               = 0;
        $this->sourceComplete      = false;
    }

    /**
     * @inheritDoc
     */
    public function __invoke(ObservableInterface $observable, ObserverInterface $observer, SchedulerInterface $scheduler = null)
    {
        $outerDisposable = new SerialDisposable();
        $this->disposable->add($outerDisposable);

        $subscribe = function () use ($outerDisposable, $observable, $observer, $scheduler, &$subscribe) {
            $this->sourceComplete = false;
            $outerSubscription    = $observable->subscribe(new CallbackObserver(
                [$observer, "onNext"],
                [$observer, "onError"],
                function () use ($observer, &$subscribe, $outerDisposable) {
                    $this->sourceComplete = true;
                    if (!$this->repeat) {
                        $observer->onCompleted();
                        return;
                    }
                    $outerDisposable->setDisposable(new EmptyDisposable());
                    $this->completions->onNext(++$this->count);
                }
            ), $scheduler);

            $outerDisposable->setDisposable($outerSubscription);
        };

        $notifierDisposable = $this->notifier->subscribe(new CallbackObserver(
            function () use (&$subscribe, $scheduler) {
                $subscribe();
            },
            function ($ex) use ($observer) {
                $this->repeat = false;
                $observer->onError($ex);
            },
            function () use ($observer) {
                $this->repeat = false;
                if ($this->sourceComplete) {
                    $observer->onCompleted();
                }
            }
        ), $scheduler);

        $this->disposable->add($notifierDisposable);

        try {
            $handled = call_user_func($this->notificationHandler, $this->completions->asObservable());

            $handledDisposable = $handled->subscribe($this->notifier, $scheduler);
            $this->disposable->add($handledDisposable);
        } catch (\Exception $e) {
            $observer->onError($e);
            return new EmptyDisposable();
        }

        $subscribe();

        return $this->disposable;
    }
}
