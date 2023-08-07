<?php

declare(strict_types = 1);

namespace Rx;

/**
 * @template-covariant T
 */
interface ObservableInterface
{
    /**
     * @param (callable(T): void)|ObserverInterface|null $onNextOrObserver
     * @param callable|null $onError
     * @param callable|null $onCompleted
     * @return DisposableInterface
     * @throws \InvalidArgumentException
     */
    public function subscribe($onNextOrObserver = null, callable  $onError = null, callable $onCompleted = null): DisposableInterface;
}
