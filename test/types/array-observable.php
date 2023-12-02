<?php

declare(strict_types = 1);

use Rx\Observable\ArrayObservable;
use Rx\Scheduler\ImmediateScheduler;
use function PHPStan\Testing\assertType;

assertType('Rx\Observable\ArrayObservable<bool>', new ArrayObservable([true, false], new ImmediateScheduler()));
assertType('Rx\Observable\ArrayObservable<bool|int>', new ArrayObservable([true, time()], new ImmediateScheduler()));
