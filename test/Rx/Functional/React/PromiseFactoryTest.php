<?php

namespace Rx\Functional\React;

use Exception;
use React\Promise\Deferred;
use Rx\Disposable\CallbackDisposable;
use Rx\Functional\FunctionalTestCase;
use Rx\Observable\AnonymousObservable;
use Rx\Observable\BaseObservable;
use Rx\Observable\EmptyObservable;
use Rx\Observer\CallbackObserver;
use Rx\React\Promise;
use Rx\React\PromiseFactory;
use Rx\Subject\Subject;

class PromiseFactoryTest extends FunctionalTestCase
{
    /**
     * @test
     */
    public function from_promise_success()
    {
        $source = PromiseFactory::toObservable(function() {
            return Promise::resolved(42);
        });

        $results = $this->scheduler->startWithCreate(function() use ($source) {
            return $source;
        });

        $this->assertMessages(array(
            onNext(200, 42),
            onCompleted(200),
        ), $results->getMessages());
    }
}
