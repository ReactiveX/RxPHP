<?php

namespace Rx\Operator;

use Rx\ObservableInterface;
use Rx\Observer\CallbackObserver;
use Rx\ObserverInterface;
use Rx\SchedulerInterface;

class BufferWithCountOperator implements OperatorInterface
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
     */
    public function __construct($count, $skip = null)
    {
        if (!is_int($count) || $count < 1) {
            throw new \InvalidArgumentException("count must be an integer greater than or equal to 1");
        }

        if ($skip === null) {
            $skip = $count;
        }

        if (!is_int($skip) || $skip < 1) {
            throw new \InvalidArgumentException("skip must be an integer great than 0");
        }

        $this->count = $count;
        $this->skip  = $skip;
    }

    /**
     * @param ObservableInterface $observable
     * @param ObserverInterface $observer
     * @param SchedulerInterface|null $scheduler
     * @return mixed
     */
    public function __invoke(
        ObservableInterface $observable,
        ObserverInterface $observer,
        SchedulerInterface $scheduler = null
    ) {
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
        ), $scheduler);
    }
}
