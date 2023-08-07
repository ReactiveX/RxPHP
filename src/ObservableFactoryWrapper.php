<?php

declare(strict_types = 1);

namespace Rx;

use React\Promise\PromiseInterface;
use Rx\React\Promise;

/**
 * @template T
 */
final class ObservableFactoryWrapper
{
    /**
     * @var (callable(): (PromiseInterface<T>|ObservableInterface<T>))
     */
    private $selector;

    /**
     * @param (callable(): (PromiseInterface<T>|ObservableInterface<T>)) $selector
     */
    public function __construct(callable $selector)
    {
        $this->selector = $selector;
    }

    /**
     * @return Observable<T>
     * @throws \ReflectionException
     */
    public function __invoke(): Observable
    {
        $result = call_user_func_array($this->selector, func_get_args());

        if ($result instanceof PromiseInterface) {
            $result = Promise::toObservable($result);
        }

        if (!$result instanceof Observable) {
            /** @phpstan-ignore-next-line */
            $reflectCallable = new \ReflectionFunction($this->selector);
            throw new \Exception("You must return an Observable or Promise in {$reflectCallable->getFileName()} on line {$reflectCallable->getStartLine()}\n");
        }

        return $result;
    }
}
