<?php

require_once __DIR__ . '/../bootstrap.php';

Rx\Observable::range(1, 3)
    ->map(function($value) {
        if ($value == 2) {
            throw new \Exception('error');
        }
        return $value;
    })
    ->finally(function() {
        echo "Finally\n";
    })
    ->subscribe($stdoutObserver);
