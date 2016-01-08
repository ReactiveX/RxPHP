<?php

require_once __DIR__ . '/../bootstrap.php';

use Rx\Observable;

class RecursiveReturnObservable extends Observable
{
    private $value;

    /**
     * @param mixed $value Value to return.
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    public function subscribe(\Rx\ObserverInterface $observer, $scheduler = null)
    {
        return $scheduler->scheduleRecursive(function ($reschedule) use ($observer) {
            $observer->onNext($this->value);
            $reschedule();
        });
    }
}

$loop      = React\EventLoop\Factory::create();
$scheduler = new Rx\Scheduler\EventLoopScheduler($loop);

$observable = new RecursiveReturnObservable(42);
$observable->subscribe($stdoutObserver, $scheduler);

$observable = new RecursiveReturnObservable(21);
$disposable = $observable->subscribe($stdoutObserver, $scheduler);

$loop->addPeriodicTimer(0.01, function () {
    $memory    = memory_get_usage() / 1024;
    $formatted = number_format($memory, 3) . 'K';
    echo "Current memory usage: {$formatted}\n";
});

// after a second we'll dispose the 21 observable
$loop->addTimer(1.0, function () use ($disposable) {
    echo "Disposing 21 observable.\n";
    $disposable->dispose();
});

$loop->run();


// After one second...
//Next value: 21
//Next value: 42
//Next value: 21
//Next value: 42
//Next value: 21
//Disposing 21 observable.
//Next value: 42
//Next value: 42
//Next value: 42
//Next value: 42
//Next value: 42
//Current memory usage: 3,349.203K
//...
