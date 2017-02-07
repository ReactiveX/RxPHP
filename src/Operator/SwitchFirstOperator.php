<?php

declare(strict_types = 1);

namespace Rx\Operator;

use Rx\Disposable\CompositeDisposable;
use Rx\Disposable\SingleAssignmentDisposable;
use Rx\DisposableInterface;
use Rx\Observable;
use Rx\ObservableInterface;
use Rx\Observer\CallbackObserver;
use Rx\ObserverInterface;

final class SwitchFirstOperator implements OperatorInterface
{
    private $isStopped = false;
    private $hasCurrent = false;

    public function __invoke(ObservableInterface $observable, ObserverInterface $observer): DisposableInterface
    {
        $singleDisposable = new SingleAssignmentDisposable();
        $disposable       = new CompositeDisposable();

        $disposable->add($singleDisposable);

        $callbackObserver = new CallbackObserver(
            function (Observable $x) use ($disposable, $observer) {
                if ($this->hasCurrent) {
                    return;
                }
                $this->hasCurrent = true;

                $inner = new SingleAssignmentDisposable();
                $disposable->add($inner);

                $innerSub = $x->subscribe(new CallbackObserver(
                    [$observer, 'onNext'],
                    [$observer, 'onError'],
                    function () use ($disposable, $inner, $observer) {
                        $disposable->remove($inner);
                        $this->hasCurrent = false;

                        if ($this->isStopped && $disposable->count() === 1) {
                            $observer->onCompleted();
                        }
                    }
                ));

                $inner->setDisposable($innerSub);
            },
            [$observer, 'onError'],
            function () use ($disposable, $observer) {
                $this->isStopped = true;
                if (!$this->hasCurrent && $disposable->count() === 1) {
                    $observer->onCompleted();
                }
            }
        );

        $singleDisposable->setDisposable($observable->subscribe($callbackObserver));

        return $disposable;

    }
}
