<?php

require_once __DIR__ . '/../bootstrap.php';

Rx\Observable::range(1, 3)
    ->finally(function(): void {
        echo "Finally\n";
    })
    ->subscribe($stdoutObserver);
