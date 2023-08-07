<?php

declare(strict_types = 1);

namespace Rx\Observable;

use Rx\DisposableInterface;
use Rx\Observable;
use Rx\Observer\AutoDetachObserver;
use Rx\ObserverInterface;
use Rx\Disposable\CompositeDisposable;

/**
 * @template T
 * @template-extends Observable<T>
 */
class ForkJoinObservable extends Observable
{
    /**
     * @var array<Observable<T>>
     */
    private $observables;

    /**
     * @var array<T>
     */
    private $values = [];

    /**
     * @var int
     */
    private $completed = 0;

    /**
     * @var callable|null
     */
    private $resultSelector;

    /**
     * @param array<Observable<T>> $observables
     */
    public function __construct(array $observables = [], callable $resultSelector = null)
    {
        $this->observables    = $observables;
        $this->resultSelector = $resultSelector;
    }

    public function _subscribe(ObserverInterface $observer): DisposableInterface
    {
        $disposable = new CompositeDisposable();

        $len = count($this->observables);

        $autoObs = new AutoDetachObserver($observer);

        if (0 === $len) {
            $autoObs->onCompleted();
        }

        foreach ($this->observables as $i => $observable) {
            $innerDisp = $observable->subscribe(
                function ($v) use ($i) {
                    $this->values[$i] = $v;
                },
                [$autoObs, 'onError'],
                function () use ($len, $i, $autoObs) {
                    $this->completed++;

                    if (!array_key_exists($i, $this->values)) {
                        $autoObs->onCompleted();
                        return;
                    }

                    if ($this->completed !== $len) {
                        return;
                    }

                    $haveValues = count($this->values);

                    if ($haveValues === $len) {
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
                });
            $disposable->add($innerDisp);
        }

        return $disposable;
    }
}
