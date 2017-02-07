<?php

namespace Rx\Observable;

use Rx\Observable;
use Rx\Observer\AutoDetachObserver;
use Rx\Observer\CallbackObserver;
use Rx\ObserverInterface;
use Rx\Disposable\CompositeDisposable;
use Rx\Scheduler\ImmediateScheduler;
use Rx\SchedulerInterface;

class ForkJoinObservable extends Observable {

    /**
     * @var Observable[]
     */
    private $observables;

    private $values = [];

    private $completed = 0;

    private $resultSelector;

    public function __construct(array $observables = [], callable $resultSelector = null) {
        $this->observables = $observables;
        $this->resultSelector = $resultSelector;
    }

    public function subscribe(ObserverInterface $observer, $scheduler = null)
    {
        $disposable = new CompositeDisposable();

        if (null == $scheduler) {
            $scheduler = new ImmediateScheduler();
        }

        $len = count($this->observables);

        $autoObs = new AutoDetachObserver($observer);

        if (0 == $len) {
            $autoObs->onCompleted();
        }

        foreach ($this->observables as $i => $observable) {
            $innerDisp = $observable->subscribeCallback(
                function($v) use ($i) {
                    $this->values[$i] = $v;
                },
                [$autoObs, 'onError'],
                function() use ($len, $i, $autoObs) {
                    $this->completed++;

                    if (!array_key_exists($i, $this->values)) {
                        $autoObs->onCompleted();
                        return;
                    }

                    if ($this->completed != $len) {
                        return;
                    }

                    $haveValues = count($this->values);

                    if ($haveValues == $len) {
                        if ($this->resultSelector) {
                            try {
                                $value = call_user_func_array($this->resultSelector, $this->values);
                            } catch (\Exception $e) {
                                $autoObs->onError($e);
                                return;
                            }
                        } else {
                            $value = $this->values;
                        }
                        $autoObs->onNext($value);
                    }

                    $autoObs->onCompleted();
                }, $scheduler);
            $disposable->add($innerDisp);
        }

        return $disposable;
    }

}
