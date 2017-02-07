<?php

declare(strict_types = 1);

namespace Rx\Operator;

use Rx\DisposableInterface;
use Rx\ObservableInterface;
use Rx\Observer\CallbackObserver;
use Rx\ObserverInterface;

final class BufferWithCountOperator implements OperatorInterface
{
    /** @var int */
    private $count;

    /** @var */
    private $skip;

    /** @var int */
    private $index = 0;

    /**
     * BufferOperator constructor.
     * @param int $count
     * @param int $skip
     * @throws \InvalidArgumentException
     */
    public function __construct(int $count, int $skip = null)
    {
        if ($count < 1) {
            throw new \InvalidArgumentException('count must be greater than or equal to 1');
        }

        if ($skip === null) {
            $skip = $count;
        }

        if ($skip < 1) {
            throw new \InvalidArgumentException('skip must be great than 0');
        }

        $this->count = $count;
        $this->skip  = $skip;
    }

    public function __invoke(ObservableInterface $observable, ObserverInterface $observer): DisposableInterface
    {
        $currentGroups = [];

        return $observable->subscribe(new CallbackObserver(
            function ($x) use (&$currentGroups, $observer) {
                if ($this->index % $this->skip === 0) {
                    $currentGroups[] = [];
                }
                $this->index++;

                foreach ($currentGroups as $key => &$group) {
                    $group[] = $x;
                    if (count($group) === $this->count) {
                        $observer->onNext($group);
                        unset($currentGroups[$key]);
                    }
                }
            },
            function ($err) use ($observer) {
                $observer->onError($err);
            },
            function () use (&$currentGroups, $observer) {
                foreach ($currentGroups as &$group) {
                    $observer->onNext($group);
                }
                $observer->onCompleted();
            }
        ));
    }
}
