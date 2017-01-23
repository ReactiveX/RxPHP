<?php

require_once __DIR__ . '/../bootstrap.php';

Rx\Observable::range(1, 3)
    ->map(function($value) {
        if ($value == 2) {
            throw new \Exception();
        }
        return $value;
    })
    ->finallyCall(function() {
        echo "Finally\n";
    })
    ->subscribe($stdoutObserver);
