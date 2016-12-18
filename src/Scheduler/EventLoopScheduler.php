<?php

namespace Rx\Scheduler;

use Interop\Async\Loop;
use React\EventLoop\StreamSelectLoop;
use Rx\DisposableInterface;
use WyriHaximus\React\AsyncInteropLoop\ReactDriverFactory;

final class EventLoopScheduler extends VirtualTimeScheduler
{
    private $nextTimer = PHP_INT_MAX;

    private $insideInvoke = false;

    private static $loopSet = false;

    public function __construct()
    {
        parent::__construct($this->now(), function ($a, $b) {
            return $a - $b;
        });

        static::registerLoopRunner();
    }


    public function scheduleAbsoluteWithState($state, int $dueTime, callable $action): DisposableInterface
    {
        $disp = parent::scheduleAbsoluteWithState($state, $dueTime, $action);

        if (!$this->insideInvoke) {
            Loop::delay(0, [$this, 'start']);
        }

        return $disp;
    }

    public function start()
    {
        $this->clock = $this->now();

        $this->insideInvoke = true;
        while ($this->queue->count() > 0) {
            $next = $this->getNext();
            if ($next !== null) {
                if ($next->getDueTime() > $this->clock) {
                    $this->nextTimer = $next->getDueTime();
                    Loop::delay($this->nextTimer - $this->clock, [$this, "start"]);
                    break;
                }

                $next->inVoke();
            }
        }
        $this->insideInvoke = false;
    }

    /**
     * @inheritDoc
     */
    public function now(): int
    {
        return (int)floor(microtime(true) * 1000);
    }

    static private function registerLoopRunner()
    {
        $hasBeenRun = false;

        if (!static::$loopSet){
            try {
                $driver = ReactDriverFactory::createFactoryFromLoop(StreamSelectLoop::class);
                Loop::setFactory($driver);
                static::$loopSet = true;
            } catch (\RuntimeException $e) {
                //Intentionally Left Blank
            }

        }


        register_shutdown_function(function () use (&$hasBeenRun) {
            if (!$hasBeenRun) {
                Loop::get()->run();
            }
        });

        Loop::get()->defer(function () use (&$hasBeenRun) {
            $hasBeenRun = true;
        });
    }
}
