<?php

declare(strict_types = 1);

namespace Rx\Operator;

use Rx\DisposableInterface;
use Rx\ObservableInterface;
use Rx\ObserverInterface;

interface OperatorInterface
{
    /**
     * @template T
     * @param ObservableInterface<T> $observable
     */
    public function __invoke(ObservableInterface $observable, ObserverInterface $observer): DisposableInterface;
}
