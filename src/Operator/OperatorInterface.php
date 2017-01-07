<?php

namespace Rx\Operator;

use Rx\DisposableInterface;
use Rx\ObservableInterface;
use Rx\ObserverInterface;

interface OperatorInterface
{
    public function __invoke(ObservableInterface $observable, ObserverInterface $observer): DisposableInterface;
}
