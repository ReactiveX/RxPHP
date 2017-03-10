<?php

declare(strict_types = 1);

namespace Rx\Operator;

use Rx\DisposableInterface;
use Rx\ObservableInterface;
use Rx\Observer\AutoDetachObserver;
use Rx\Observer\CallbackObserver;
use Rx\ObserverInterface;

final class ConcatOperator implements OperatorInterface
{
    /** @var \Rx\ObservableInterface */
    private $subsequentObservable;

    /**
     * Concat constructor.
     * @param ObservableInterface $subsequentObservable
     */
    public function __construct(ObservableInterface $subsequentObservable)
    {
        $this->subsequentObservable = $subsequentObservable;
    }

    /**
     * @inheritDoc
     */
    public function __invoke(ObservableInterface $observable, ObserverInterface $observer): DisposableInterface
    {
        $cbObserver = new CallbackObserver(
            [$observer, 'onNext'],
            [$observer, 'onError'],
            function () use ($observer) {
                $o = new AutoDetachObserver($observer);
                $o->setDisposable($this->subsequentObservable->subscribe($o));
            }
        );

        return $observable->subscribe($cbObserver);
    }
}
