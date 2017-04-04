<?php

declare(strict_types=1);

namespace Rx\Operator;

use Rx\Disposable\CompositeDisposable;
use Rx\Disposable\SingleAssignmentDisposable;
use Rx\DisposableInterface;
use Rx\ObservableInterface;
use Rx\ObserverInterface;

final class WithLatestFromOperator implements OperatorInterface
{
    /** @var ObservableInterface[] */
    private $observables;

    /** @var callable */
    private $resultSelector;

    public function __construct(array $observables, callable $resultSelector = null)
    {
        if (null === $resultSelector) {
            $resultSelector = function () {
                return func_get_args();
            };
        }

        foreach ($observables as $observable) {
            if (!$observable instanceof ObservableInterface) {
                throw new \InvalidArgumentException;
            }
        }

        $this->observables    = $observables;
        $this->resultSelector = $resultSelector;
    }

    public function __invoke(ObservableInterface $source, ObserverInterface $observer): DisposableInterface
    {
        $observables  = &$this->observables;
        $count        = count($observables);
        $hasAllValues = false;
        $values       = [];

        $subscriptions = [];

        foreach ($observables as $key => $o) {
            $sad = new SingleAssignmentDisposable();

            $subscription = $o->subscribe(
                function ($value) use ($key, &$values, $count, &$hasAllValues) {
                    $values[$key] = $value;
                    $hasAllValues = $count === count($values);
                },
                [$observer, 'onError'],
                function () {
                    //noop
                });

            $sad->setDisposable($subscription);
            $subscriptions[$key] = $sad;
        }

        $outerSad = new SingleAssignmentDisposable();
        $outerSad->setDisposable($source->subscribe(
            function ($value) use ($observer, &$values, &$hasAllValues) {
                ksort($values);
                $allValues = array_merge([$value], $values);

                if (!$hasAllValues) {
                    return;
                }

                try {
                    $res = call_user_func_array($this->resultSelector, $allValues);
                    $observer->onNext($res);
                } catch (\Throwable $ex) {
                    $observer->onError($ex);
                }
            },
            [$observer, 'onError'],
            [$observer, 'onCompleted']));
        $subscriptions[] = $outerSad;

        return new CompositeDisposable($subscriptions);
    }
}
