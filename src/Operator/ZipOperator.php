<?php

declare(strict_types = 1);

namespace Rx\Operator;

use Rx\Disposable\CompositeDisposable;
use Rx\DisposableInterface;
use Rx\ObservableInterface;
use Rx\Observer\CallbackObserver;
use Rx\ObserverInterface;

final class ZipOperator implements OperatorInterface
{
    /** @var \SplQueue[] */
    private array $queues = [];
    private int $completesRemaining = 0;
    private ?int $numberOfSources = null;
    /** @var bool[] */
    private array $completed = [];

    public function __construct(
        /** @var ObservableInterface[] */
        private array $sources,
        private null|\Closure $resultSelector = null
    ) {

        if ($resultSelector === null) {
            $resultSelector = function (): array {
                return func_get_args();
            };
        }
        $this->resultSelector = $resultSelector;
    }

    public function __invoke(ObservableInterface $observable, ObserverInterface $observer): DisposableInterface
    {
        array_unshift($this->sources, $observable);

        $this->numberOfSources = count($this->sources);

        $disposable = new CompositeDisposable();

        $this->completesRemaining = $this->numberOfSources;

        for ($i = 0; $i < $this->numberOfSources; $i++) {
            $this->queues[$i]    = new \SplQueue();
            $this->completed[$i] = false;
        }

        for ($i = 0; $i < $this->numberOfSources; $i++) {
            $source = $this->sources[$i];

            $cbObserver = new CallbackObserver(
                function ($x) use ($i, $observer): void {
                    // if there is another item in the sequence after one of the other source
                    // observables completes, we need to complete at this time to match the
                    // behavior of RxJS
                    if ($this->completesRemaining < $this->numberOfSources) {
                        // check for completed and empty queues
                        for ($j = 0; $j < $this->numberOfSources; $j++) {
                            if ($this->completed[$j] && count($this->queues[$j]) === 0) {
                                $observer->onCompleted();
                                return;
                            }
                        }
                    }

                    $this->queues[$i]->enqueue($x);

                    for ($j = 0; $j < $this->numberOfSources; $j++) {
                        if ($this->queues[$j]->isEmpty()) {
                            return;
                        }
                    }

                    $params = [];
                    for ($j = 0; $j < $this->numberOfSources; $j++) {
                        $params[] = $this->queues[$j]->dequeue();
                    }

                    try {
                        $observer->onNext(call_user_func_array($this->resultSelector, $params));
                    } catch (\Throwable $e) {
                        $observer->onError($e);
                    }
                },
                function ($e) use ($observer): void {
                    $observer->onError($e);
                },
                function () use ($i, $observer): void {
                    $this->completesRemaining--;
                    $this->completed[$i] = true;
                    if ($this->completesRemaining === 0) {
                        $observer->onCompleted();
                    }
                }
            );

            $subscription = $source->subscribe($cbObserver);

            $disposable->add($subscription);
        }

        return $disposable;
    }
}
