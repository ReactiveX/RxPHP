<?php

use Rx\Disposable\CallbackDisposable;

require_once __DIR__ . '/../bootstrap.php';

//With Class
$source = new \Rx\Observable\AnonymousObservable(function (\Rx\ObserverInterface $observer) {
    $observer->onNext(42);
    $observer->onCompleted();

    return new CallbackDisposable(function () {
        echo "Disposed\n";
    });
});

$subscription = $source->subscribe($createStdoutObserver());
