<?php

use Rx\Observable;

$maxRetryCount = 25;
$newRetryCount = 0;

$source = Observable::range(5, 1)
    ->flatMap(function($x) use (&$maxRetryCount, &$newRetryCount) {
        if (++$newRetryCount < $maxRetryCount - 1) {
            return Observable::error(new \Exception('error'));
        }
        return Observable::just($x);
    })
    ->retry($maxRetryCount);

return function() use ($source) {
    return $source;
};
