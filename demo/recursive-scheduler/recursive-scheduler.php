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

        $observers = $this->observers;

        return $scheduler->scheduleRecursive(function($reschedule) use ($observers, $value) {
            foreach ($observers as $observer) {
                $observer->onNext($value);
            }

            $reschedule();
        });
    }
}

$loop = React\EventLoop\Factory::create();
$scheduler = new Rx\Scheduler\EventLoopScheduler($loop);

$observable = new RecursiveReturnObservable(42);
$observable->subscribe($stdoutObserver, $scheduler);
$observable = new RecursiveReturnObservable(21);
$disposable = $observable->subscribe($stdoutObserver, $scheduler);

$loop->addPeriodicTimer(0.01, function () {
    $memory = memory_get_usage() / 1024;
    $formatted = number_format($memory, 3).'K';
    echo "Current memory usage: {$formatted}\n";
});

// after a second we'll dispose the 21 observable
$loop->addTimer(1.0, function () use ($disposable) {
    echo "Disposing 21 observable.\n";
    $disposable->dispose();
});

$loop->run();
