<?php

require_once __DIR__ . '/../bootstrap.php';

function gen_one_to_three()
{
    for ($i = 1; $i <= 3; $i++) {
        yield $i;
    }
}

$generator = gen_one_to_three();
$source    = new \Rx\Observable\IteratorObservable($generator);

$source->subscribe($stdoutObserver);
