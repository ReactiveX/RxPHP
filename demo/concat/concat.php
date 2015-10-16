<?php

require_once __DIR__ . '/../bootstrap.php';


$source1 = \Rx\Observable\BaseObservable::just(42);
$source2 = \Rx\Observable\BaseObservable::just(56);

$source = (new \Rx\Observable\EmptyObservable())->concat($source1)->concat($source2);

$subscription = $source->subscribe($stdoutObserver);

//Next value: 42
//Next value: 56
//Complete!


