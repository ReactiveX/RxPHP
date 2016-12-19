<?php
require_once __DIR__ . '/../bootstrap.php';

\Rx\Observable::interval(1000)
    ->take(5)
    ->subscribe($createStdoutObserver());
