<?php

declare(strict_types = 1);

use Rx\React\Promise;

use function PHPStan\Testing\assertType;
use function React\Promise\resolve;

assertType('Rx\Observable<bool>', Promise::toObservable(resolve(false)));
