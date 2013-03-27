<?php

namespace Rx\Scheduler;

use Rx\Disposable\EmptyDisposable;
use Rx\Disposable\CompositeDisposable;
use InvalidArgumentException;

class ImmediateScheduler
{
    public function schedule($action)
    {
        if ( ! is_callable($action)) {
            throw new InvalidArgumentException("Action should be a callable.");
        }

        $action();

        return new EmptyDisposable();
    }

    public function scheduleRecursive($action)
    {
        if ( ! is_callable($action)) {
            throw new InvalidArgumentException("Action should be a callable.");
        }

        $group = new CompositeDisposable();
        $scheduler = $this;

        $recursiveAction = null;
        $recursiveAction = function() use ($action, &$scheduler, &$group, &$recursiveAction) {
            $action(
                function() use (&$scheduler, &$group, &$recursiveAction) {
                    $isAdded = false;
                    $isDone  = true;

                    $d = $scheduler->schedule(function() use (&$isAdded, &$isDone, &$group, &$recursiveAction) {
                        $recursiveAction();

                        if ($isAdded) {
                            $group->remove($d);
                        } else {
                            $isDone = true;
                        }
                    });

                    if ( ! $isDone) {
                        $group->add($d);
                        $isAdded = true;
                    }
                }
            );
        };

        $group->add($this->schedule($recursiveAction()));

        return $group;
    }
}
