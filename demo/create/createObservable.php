<?php

use Rx\Disposable\CallbackDisposable;
use Rx\Observable\AnonymousObservable;
use Rx\ObserverInterface;

require_once __DIR__ . '/../bootstrap.php';

//With Class
$source = new AnonymousObservable(function (ObserverInterface $observer) {
    $observer->onNext(42);
    $observer->onCompleted();

    return new CallbackDisposable(function () {
        echo "Disposed\n";
    });
});

$subscription = $source->subscribe($createStdoutObserver());
