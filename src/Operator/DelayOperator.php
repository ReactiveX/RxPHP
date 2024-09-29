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
    private \SplQueue $queue;

    /** @var DisposableInterface */
    private $schedulerDisposable;

    public function __construct(
        private readonly int                     $delayTime,
        private readonly AsyncSchedulerInterface $scheduler
    ) {
        $this->queue     = new \SplQueue();
    }

    public function __invoke(ObservableInterface $observable, ObserverInterface $observer): DisposableInterface
    {
        /** @var AnonymousObservable $observable */
        $disp = $observable
            ->materialize()
            ->timestamp($this->scheduler)
            ->map(function (Timestamped $x): \Rx\Timestamped {
                return new Timestamped($x->getTimestampMillis() + $this->delayTime, $x->getValue());
            })
            ->subscribe(new CallbackObserver(
                function (Timestamped $x) use ($observer): void {
                    if ($x->getValue() instanceof Notification\OnErrorNotification) {
                        $x->getValue()->accept($observer);
                        return;
                    }
                    $this->queue->enqueue($x);
                    if ($this->schedulerDisposable === null) {
                        $doScheduledStuff = function () use ($observer, &$doScheduledStuff): void {
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

        return new CallbackDisposable(function () use ($disp): void {
            if ($this->schedulerDisposable) {
                $this->schedulerDisposable->dispose();
            }
            $disp->dispose();
        });
    }
}
