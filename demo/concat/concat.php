<?php

require_once __DIR__ . '/../bootstrap.php';


$source1 = \Rx\Observable::of(42);
$source2 = \Rx\Observable::of(56);

$source = \Rx\Observable::empty()->concat($source1)->concat($source2);

$subscription = $source->subscribe($stdoutObserver);
