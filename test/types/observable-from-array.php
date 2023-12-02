<?php

declare(strict_types = 1);

use Rx\Observable;
use function PHPStan\Testing\assertType;

assertType('Rx\Observable<bool>', Observable::fromArray([true, false]));
