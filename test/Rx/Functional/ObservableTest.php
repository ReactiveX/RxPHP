<?php

namespace Rx\Functional;

use Rx\Observer\TestException;
use Rx\Testing\MockObserver;

class ObservableTest extends FunctionalTestCase
{
    public function testExceptionInOnNextByDefaultGoesToErrorAndDisposes(): void
    {
        $xs = $this->createHotObservable(
            [
                onNext(150, 1),
                onNext(210, 2),
                onNext(220, 3),
                onNext(230, 4),
                onCompleted(250)
            ]);

        $results = new MockObserver($this->scheduler);

        $disposable = null;

        $this->scheduler->scheduleAbsolute(200, function () use ($xs, $results, &$disposable): void {
            $disposable = $xs->subscribe(
                function ($value) use ($results): void {
                    if ($value === 3) {
                        throw new TestException();
                    }
                    $results->onNext($value);
                },
                [$results, 'onError'],
                [$results, 'onCompleted']
            );
        });

        $this->scheduler->scheduleAbsolute(1000, function () use (&$disposable): void {
            $disposable->dispose();
        });

        $this->scheduler->start();

        $this->assertMessages(
            [
                onNext(210, 2),
                onError(220, new TestException()),
            ],
            $results->getMessages());

        $this->assertSubscriptions([subscribe(200, 220)], $xs->getSubscriptions());
    }
}
