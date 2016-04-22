<?php

use Rx\Disposable\CallbackDisposable;

require_once __DIR__ . '/../bootstrap.php';

//With static method
$source = \Rx\Observable::create(function (\Rx\ObserverInterface $observer) {
    $observer->onNext(42);
    $observer->onCompleted();

    return new CallbackDisposable(function () {
        echo "Disposed\n";
    });
});

$subscription = $source->subscribe($createStdoutObserver());
