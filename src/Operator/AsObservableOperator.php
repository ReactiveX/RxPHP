<?php

declare(strict_types = 1);

namespace Rx\Operator;

use Rx\DisposableInterface;
use Rx\ObservableInterface;
use Rx\ObserverInterface;

final class AsObservableOperator implements OperatorInterface
{
    public function __invoke(ObservableInterface $observable, ObserverInterface $observer): DisposableInterface
    {
        return $observable->subscribe($observer);
    }
}
