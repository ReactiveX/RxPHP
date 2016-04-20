<?php

require_once __DIR__ . '/../bootstrap.php';


$source1 = \Rx\Observable::just(42);
$source2 = \Rx\Observable::just(56);

$source = \Rx\Observable::emptyObservable()->concat($source1)->concat($source2);

$subscription = $source->subscribe($stdoutObserver);
