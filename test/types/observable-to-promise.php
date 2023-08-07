<?php

declare(strict_types = 1);

use Rx\Observable;
use Rx\Observable\ReturnObservable;
use Rx\Scheduler;
use function PHPStan\Testing\assertType;
use function React\Promise\resolve;

assertType('React\Promise\PromiseInterface<bool>', Observable::of(true)->toPromise());
