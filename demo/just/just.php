<?php

require_once __DIR__.'/../bootstrap.php';


$source =  \Rx\Observable::just(42);

$subscription = $source->subscribe($stdoutObserver);
