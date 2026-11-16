<?php

declare(strict_types = 1);

use Rx\Observable\ArrayObservable;
use Rx\Scheduler\ImmediateScheduler;
use function PHPStan\Testing\assertType;

assertType('Rx\Observable\ArrayObservable<bool>', new ArrayObservable([true, false], new ImmediateScheduler()));
assertType('Rx\Observable\ArrayObservable<bool|int<1, max>>', new ArrayObservable([true, time(), false], new ImmediateScheduler()));
assertType('Rx\Observable\ArrayObservable<bool|int<-9223372036854775808, 9223372036854775807>>', new ArrayObservable([true, random_int(PHP_INT_MIN, PHP_INT_MAX), false], new ImmediateScheduler()));
