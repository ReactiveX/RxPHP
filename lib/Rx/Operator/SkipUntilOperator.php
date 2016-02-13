<?php


namespace Rx\Operator;

use Rx\Disposable\CompositeDisposable;
use Rx\Disposable\EmptyDisposable;
use Rx\DisposableInterface;
use Rx\ObservableInterface;
use Rx\Observer\CallbackObserver;
use Rx\ObserverInterface;
use Rx\SchedulerInterface;


class SkipUntilOperator implements OperatorInterface
{
    /** @var ObservableInterface */
    private $other;

    public function __construct(ObservableInterface $other)
    {
        $this->other = $other;
    }

    /**
     * @inheritDoc
     */
    public function __invoke(ObservableInterface $observable, ObserverInterface $observer, SchedulerInterface $scheduler = null)
    {
        $isOpen = false;

        $otherDisposable = new EmptyDisposable();

        /** @var DisposableInterface $otherDisposable */
        $otherDisposable = $this->other->subscribe(new CallbackObserver(
            function ($x) use (&$isOpen, &$otherDisposable) {
                $isOpen = true;
                $otherDisposable->dispose();
            },
            function ($e) use ($observer) {
                $observer->onError($e);
            },
            function () use (&$otherDisposable) {
                $otherDisposable->dispose();
            }
        ), $scheduler);


        $sourceDisposable = $observable->subscribe(new CallbackObserver(
            function ($x) use ($observer, &$isOpen) {
                if ($isOpen) {
                    $observer->onNext($x);
                }
            },
            function ($e) use ($observer) {
                $observer->onError($e);
            },
            function () use ($observer, &$isOpen) {
                if ($isOpen) {
                    $observer->onCompleted();
                }
            }
        ), $scheduler);

        return new CompositeDisposable([$otherDisposable, $sourceDisposable]);

    }
}
