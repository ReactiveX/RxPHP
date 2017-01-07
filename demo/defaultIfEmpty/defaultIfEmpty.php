<?php

require_once __DIR__ . '/../bootstrap.php';

$source = \Rx\Observable::empty()->defaultIfEmpty(Rx\Observable::of('something'));

$subscription = $source->subscribe($stdoutObserver);
