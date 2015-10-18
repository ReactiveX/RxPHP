<?php

require_once __DIR__.'/../bootstrap.php';


$p = \Rx\React\Promise::resolved(42);
$source = \Rx\React\Promise::toObservable($p);

$subscription = $source->subscribe($stdoutObserver);

//Next value: 42
//Complete!



// Create a promise which rejects with an error
$p = \Rx\React\Promise::rejected(new Exception('because'));

$source2 = \Rx\React\Promise::toObservable($p);

$subscription = $source2->subscribe($createStdoutObserver());

//Exception: because
