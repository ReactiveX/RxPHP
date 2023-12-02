<?php

declare(strict_types = 1);

use Rx\Observable;
use Rx\Observable\ReturnObservable;
use Rx\Scheduler\ImmediateScheduler;
use function PHPStan\Testing\assertType;

assertType('Rx\Observable\ReturnObservable<bool>', new ReturnObservable(true, new ImmediateScheduler()));
assertType('Rx\Observable<bool>', Observable::of(true));
assertType('Rx\Observable<bool>', Observable::of(false));
assertType('Rx\Observable<int>', Observable::of(time()));
