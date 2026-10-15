<?php

declare(strict_types = 1);

use Rx\Observable;
use function PHPStan\Testing\assertType;
use function React\Promise\resolve;

assertType('Rx\Observable<bool>', Observable::fromPromise(resolve(false)));
