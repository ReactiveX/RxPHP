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
    private Subject $completions;
    private Subject $notifier;
    private CompositeDisposable $disposable;
    private bool $repeat = true;
    private int $count = 0;
    private bool $sourceComplete = false;

    public function __construct(private $notificationHandler)
    {
        $this->completions         = new Subject();
        $this->disposable          = new CompositeDisposable();
        $this->notifier            = new Subject();
    }

    public function __invoke(ObservableInterface $observable, ObserverInterface $observer): DisposableInterface
    {
        $outerDisposable = new SerialDisposable();
        $this->disposable->add($outerDisposable);

        $subscribe = function () use ($outerDisposable, $observable, $observer, &$subscribe): void {
            $this->sourceComplete = false;
            $outerSubscription    = $observable->subscribe(new CallbackObserver(
                fn ($x) => $observer->onNext($x),
                fn ($err) => $observer->onError($err),
                function () use ($observer, &$subscribe, $outerDisposable): void {
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
            function () use (&$subscribe): void {
                $subscribe();
            },
            function ($ex) use ($observer): void {
                $this->repeat = false;
                $observer->onError($ex);
            },
            function () use ($observer): void {
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
