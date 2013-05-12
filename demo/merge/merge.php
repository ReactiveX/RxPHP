<?php

require_once __DIR__ . '/../bootstrap.php';

use Rx\Observable\BaseObservable;

class RecursiveReturnObservable extends BaseObservable
{
    private $value;

    /**
     * @param mixed $value Value to return.
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    protected function doStart($scheduler)
    {
        $value     = $this->value;

        $observers = &$this->observers;

        return $scheduler->scheduleRecursive(function($reschedule) use (&$observers, $value) {
            foreach ($observers as $observer) {
                $observer->onNext($value);
            }

            $reschedule();
        });
    }
}

$loop = React\EventLoop\Factory::create();
$scheduler = new Rx\Scheduler\EventLoopScheduler($loop);

$observable       = new RecursiveReturnObservable(42);
$otherObservable  = new RecursiveReturnObservable(21);
$mergedObservable = $observable->merge($otherObservable, $scheduler);

$disposable = $mergedObservable->subscribe($stdoutObserver, $scheduler);

$loop->addPeriodicTimer(0.01, function () {
    $memory = memory_get_usage() / 1024;
    $formatted = number_format($memory, 3).'K';
    echo "Current memory usage: {$formatted}\n";
});

$loop->run();
