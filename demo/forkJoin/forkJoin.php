<?php

require_once __DIR__ . '/../bootstrap.php';

use Rx\Observable;

$obs1 = Observable::range(1, 4);
$obs2 = Observable::range(3, 5);
$obs3 = Observable::fromArray(['a', 'b', 'c']);

$observable = Observable::forkJoin([$obs1, $obs2, $obs3], function($v1, $v2, $v3) {
    return $v1 . $v2 . $v3;
});
$observable->subscribe($stdoutObserver);