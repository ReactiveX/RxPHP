<?php

namespace Rx\Operator;

use Rx\DisposableInterface;
use Rx\ObservableInterface;
use Rx\ObserverInterface;

class OfType implements OperatorInterface
{
    private $typeNames;
    private $errorIfNotMatched;

    public function __construct($typeNames, $errorIfNotMatched = false)
    {
        if (is_string($typeNames)) {
            $typeNames = [$typeNames];
        }
        if (!is_array($typeNames)) {
            throw new \InvalidArgumentException('$typeName argument to OfType operator must be a string or an array of strings');
        }
        $typeNames = array_values($typeNames);
        if (count($typeNames) !== array_reduce(
            $typeNames,
            function ($acc, $value) {
                return $acc + (is_string($value) ? 1 : 0);
            },
            0
            )
        ) {
            throw new \InvalidArgumentException('$typeName argument to OfType operator must be a string or an array of strings');
        }
        $this->typeNames         = $typeNames;
        $this->errorIfNotMatched = $errorIfNotMatched;
    }

    public function __invoke(ObservableInterface $observable, ObserverInterface $observer): DisposableInterface
    {
        $filter = function ($x) {

        }

        return $observable
            ->filter()
            ->subscribe($observer);
    }
}