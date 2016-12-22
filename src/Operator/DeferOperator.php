<?php

namespace Rx\Operator;

use Rx\DisposableInterface;
use Rx\Observable;
use Rx\ObservableInterface;
use Rx\ObserverInterface;

final class DeferOperator implements OperatorInterface
{

    /* @var Callable */
    private $factory;

    public function __construct(callable $factory)
    {
        $this->factory = $factory;
    }

    public function __invoke(ObservableInterface $observable, ObserverInterface $observer): DisposableInterface
    {
        $factory = $this->factory;

        try {
            $result = $factory();
        } catch (\Exception $e) {
            return Observable::error($e)->subscribe($observer);
        }

        return $result->subscribe($observer);
    }
}
