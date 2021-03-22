<?php

use Rx\Observable;
use Rx\Scheduler\ImmediateScheduler;

$scheduler = new ImmediateScheduler();

$maxRetryCount = 25;
$newRetryCount = 0;

$source = Observable::range(5, 1, $scheduler)
    ->flatMap(function($x) use (&$maxRetryCount, &$newRetryCount) {
        if (++$newRetryCount < $maxRetryCount - 1) {
            return Observable::error(new \Exception('error'));
        }
        return Observable::of($x);
    })
    ->retry($maxRetryCount);

return function() use ($source) {
    return $source;
};
