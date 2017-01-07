<?php

require_once __DIR__.'/../bootstrap.php';


$source =  \Rx\Observable::of(42);

$subscription = $source->subscribe($stdoutObserver);
