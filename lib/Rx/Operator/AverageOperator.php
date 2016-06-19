<?php

namespace Rx\Operator;

use Rx\ObservableInterface;
use Rx\Observer\CallbackObserver;
use Rx\ObserverInterface;
use Rx\SchedulerInterface;

class AverageOperator implements OperatorInterface
{
    const UNEXPECTED_VALUE_THROW_EXCEPTION = 0;
    const UNEXPECTED_VALUE_IGNORE = 1;
    const UNEXPECTED_VALUE_CAST = 2;

    private $conflictResolving;

    /**
     * SumOperator constructor.
     */
    public function __construct($conflictResolving = self::UNEXPECTED_VALUE_THROW_EXCEPTION)
    {
        if (!in_array($conflictResolving, array(
            self::UNEXPECTED_VALUE_CAST,
            self::UNEXPECTED_VALUE_IGNORE,
            self::UNEXPECTED_VALUE_THROW_EXCEPTION))
        ) {
            throw new \InvalidArgumentException('Invalid conflict resolving type');
        }
        $this->conflictResolving = $conflictResolving;
    }

    /**
     * @inheritDoc
     */
    public function __invoke(
        ObservableInterface $observable,
        ObserverInterface $observer,
        SchedulerInterface $scheduler = null
    )
    {
        $accumulator = 0;
        $counter = 0;
        return $observable->subscribe(new CallbackObserver(
            function ($x) use ($observer, &$accumulator, &$counter) {
                if (!is_numeric($x)) {
                    switch ($this->conflictResolving) {
                        case self::UNEXPECTED_VALUE_THROW_EXCEPTION:
                            $observer->onError(new \UnexpectedValueException('Element must be numeric'));
                            break;
                        case self::UNEXPECTED_VALUE_IGNORE:
                            $x = 0;
                            $counter--;
                            break;
                        case self::UNEXPECTED_VALUE_CAST:
                        default:
                    }
                }
                try {
                    $accumulator += $x;
                    $counter++;
                } catch (\Exception $e) {
                    $observer->onError($e);
                }
            },
            [$observer, 'onError'],
            function () use (&$accumulator, $observer, &$counter) {
                if($counter == 0){
                    $observer->onError(new \RuntimeException('Sequence can not be empty'));
                }else {
                    $observer->onNext($accumulator / $counter);
                    $observer->onCompleted();
                }
                return;
            }
        ), $scheduler);
    }
}