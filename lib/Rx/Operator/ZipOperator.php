<?php

namespace Rx\Operator;

use Rx\Disposable\CompositeDisposable;
use Rx\ObservableInterface;
use Rx\Observer\CallbackObserver;
use Rx\ObserverInterface;
use Rx\SchedulerInterface;

class ZipOperator implements OperatorInterface {
    /** @var ObservableInterface[] */
    private $sources;
    
    /** @var callable */
    private $resultSelector;

    /** @var \SplQueue[] */
    private $queues = [];

    /** @var int */
    private $completesRemaining = 0;

    public function __construct(array $sources, callable $resultSelector = null)
    {
        $this->sources = $sources;

        if ($resultSelector === null) {
            $resultSelector = function () {
                return func_get_args();
            };
        }
        $this->resultSelector = $resultSelector;
    }

    /**
     * @inheritDoc
     */
    public function __invoke(
        ObservableInterface $observable,
        ObserverInterface $observer,
        SchedulerInterface $scheduler = null
    ) {
        array_unshift($this->sources, $observable);

        $disposable = new CompositeDisposable();

        $this->completesRemaining = count($this->sources);

        for ($i = 0; $i < count($this->sources); $i++) {
            $this->queues[$i] = new \SplQueue();
        }

        for($i=0;$i<count($this->sources);$i++) {
            $source = $this->sources[$i];

            $disposable->add($source->subscribe(new CallbackObserver(
                function ($x) use ($i, $observer) {
                    // if there is another item in the sequence after one of the other source
                    // observables completes, we need to complete at this time to match the
                    // behavior of RxJS
                    if ($this->completesRemaining < count($this->sources)) {
                        $observer->onCompleted();
                        return;
                    }

                    $this->queues[$i]->enqueue($x);

                    for ($j = 0; $j < count($this->queues); $j++) {
                        if ($this->queues[$j]->isEmpty()) {
                            return;
                        }
                    }

                    $params = [];
                    for ($j = 0; $j < count($this->queues); $j++) {
                        $queue = $this->queues[$j];
                        $params[] = $queue->dequeue();
                    }

                    $selector = $this->resultSelector;

                    try {
                        $observer->onNext(call_user_func_array($selector, $params));
                    } catch (\Exception $e) {
                        $observer->onError($e);
                    }
                },
                function ($e) use ($observer) {
                    $observer->onError($e);
                },
                function () use ($i, $observer) {
                    $this->completesRemaining--;
                    if ($this->completesRemaining == 0) {
                        $observer->onCompleted();
                    }
                }
            )));
        }
        
        return $disposable;
    }
}
