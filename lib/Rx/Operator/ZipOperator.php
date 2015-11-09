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

    public function __construct(array $sources, callable $resultSelector)
    {
        $this->sources= $sources;
        $this->resultSelector= $resultSelector;
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

        for ($i = 0; $i < count($this->sources); $i++) {
            $this->queues[$i] = new \SplQueue();
        }

        for($i=0;$i<count($this->sources);$i++) {
            $source = $this->sources[$i];

            $disposable->add($source->subscribe(new CallbackObserver(
                function ($x) use ($i, $observer) {
                    $this->queues[$i]->enqueue($x);

                    for ($j = 0; $j < count($this->queues); $j++) {
                        if ($this->queues[$j]->isEmpty()) {
                            return;
                        }
                    }
                    //echo "counts = " . $this->queues[0]->count() . ", " . $this->queues[1]->count() . "\n";

                    $params = [];
                    for ($j = 0; $j < count($this->queues); $j++) {
                        $queue = $this->queues[$j];
                        $params[] = $queue->dequeue();
                    }

                    $selector = $this->resultSelector;

                    $observer->onNext(call_user_func_array($selector, $params));
                },
                function ($e) use ($observer) {
                    $observer->onError($e);
                },
                function () use ($i, $observer) {
                    if ($this->queues[$i]->isEmpty()) {
                        $observer->onCompleted();
                    }
                }
            )));
        }
        
        return $disposable;
    }
}
