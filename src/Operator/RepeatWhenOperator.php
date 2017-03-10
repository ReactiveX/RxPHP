<?php

declare(strict_types = 1);

namespace Rx\Operator;

use Rx\Disposable\CompositeDisposable;
use Rx\Disposable\EmptyDisposable;
use Rx\Disposable\SerialDisposable;
use Rx\DisposableInterface;
use Rx\Observer\CallbackObserver;
use Rx\ObservableInterface;
use Rx\ObserverInterface;
use Rx\Subject\Subject;

final class RepeatWhenOperator implements OperatorInterface
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

    public function __invoke(ObservableInterface $observable, ObserverInterface $observer): DisposableInterface
    {
        $outerDisposable = new SerialDisposable();
        $this->disposable->add($outerDisposable);

        $subscribe = function () use ($outerDisposable, $observable, $observer, &$subscribe) {
            $this->sourceComplete = false;
            $outerSubscription    = $observable->subscribe(new CallbackObserver(
                [$observer, 'onNext'],
                [$observer, 'onError'],
                function () use ($observer, &$subscribe, $outerDisposable) {
                    $this->sourceComplete = true;
                    if (!$this->repeat) {
                        $observer->onCompleted();
                        return;
                    }
                    $outerDisposable->setDisposable(new EmptyDisposable());
                    $this->completions->onNext(++$this->count);
                }
            ));

            $outerDisposable->setDisposable($outerSubscription);
        };

        $notifierDisposable = $this->notifier->subscribe(new CallbackObserver(
            function () use (&$subscribe) {
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
        ));

        $this->disposable->add($notifierDisposable);

        try {
            $handled = ($this->notificationHandler)($this->completions->asObservable());

            $handledDisposable = $handled->subscribe($this->notifier);
            $this->disposable->add($handledDisposable);
        } catch (\Throwable $e) {
            $observer->onError($e);
            return new EmptyDisposable();
        }

        $subscribe();

        return $this->disposable;
    }
}
