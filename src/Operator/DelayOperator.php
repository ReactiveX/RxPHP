<?php

declare(strict_types = 1);

namespace Rx\Operator;

use Rx\Disposable\CallbackDisposable;
use Rx\DisposableInterface;
use Rx\Notification;
use Rx\Observable\AnonymousObservable;
use Rx\ObservableInterface;
use Rx\Observer\CallbackObserver;
use Rx\ObserverInterface;
use Rx\AsyncSchedulerInterface;
use Rx\Timestamped;

final class DelayOperator implements OperatorInterface
{
    /** @var int */
    private $delayTime;

    /** @var \SplQueue */
    private $queue;

    /** @var DisposableInterface */
    private $schedulerDisposable;

    /** @var AsyncSchedulerInterface */
    private $scheduler;

    public function __construct(int $delayTime, AsyncSchedulerInterface $scheduler)
    {
        $this->delayTime = $delayTime;
        $this->queue     = new \SplQueue();
        $this->scheduler = $scheduler;
    }

    public function __invoke(ObservableInterface $observable, ObserverInterface $observer): DisposableInterface
    {
        /** @var AnonymousObservable $observable */
        $disp = $observable
            ->materialize()
            ->timestamp($this->scheduler)
            ->map(function (Timestamped $x) {
                return new Timestamped($x->getTimestampMillis() + $this->delayTime, $x->getValue());
            })
            ->subscribe(new CallbackObserver(
                function (Timestamped $x) use ($observer) {
                    if ($x->getValue() instanceof Notification\OnErrorNotification) {
                        $x->getValue()->accept($observer);
                        return;
                    }
                    $this->queue->enqueue($x);
                    if ($this->schedulerDisposable === null) {
                        $doScheduledStuff = function () use ($observer, &$doScheduledStuff) {
                            while ((!$this->queue->isEmpty()) && $this->scheduler->now() >= $this->queue->bottom()->getTimestampMillis()) {
                                /** @var Timestamped $item */
                                $item = $this->queue->dequeue();
                                /** @var Notification $materializedValue */
                                $materializedValue = $item->getValue();
                                $materializedValue->accept($observer);
                            }

                            if ($this->queue->isEmpty()) {
                                $this->schedulerDisposable = null;
                                return;
                            }
                            $this->schedulerDisposable = $this->scheduler->schedule(
                                $doScheduledStuff,
                                $this->queue->bottom()->getTimestampMillis() - $this->scheduler->now()
                            );
                        };

                        $doScheduledStuff();
                    }
                },
                [$observer, 'onError']
            ));

        return new CallbackDisposable(function () use ($disp) {
            if ($this->schedulerDisposable) {
                $this->schedulerDisposable->dispose();
            }
            $disp->dispose();
        });
    }
}
