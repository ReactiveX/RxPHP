<?php

require_once __DIR__ . '/../bootstrap.php';

Rx\Observable::range(1, 3)
    ->doFinally(function() {
        echo "Finally\n";
    })
    ->subscribe($stdoutObserver);
