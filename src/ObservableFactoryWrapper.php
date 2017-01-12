<?php

declare(strict_types = 1);

namespace Rx;

final class ObservableFactoryWrapper
{
    private $selector;

    public function __construct(callable $selector)
    {
        $this->selector = $selector;
    }

    public function __invoke(): Observable
    {
        $result = call_user_func_array($this->selector, func_get_args());

        if (!$result instanceof ObservableInterface) {
            $reflectCallable = new \ReflectionFunction($this->selector);
            throw new \Exception("You must return an Observable or Promise in {$reflectCallable->getFileName()} on line {$reflectCallable->getStartLine()}\n");
        }

        return $result;
    }
}
