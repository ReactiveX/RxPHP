<?php

declare(strict_types = 1);

namespace Rx\Functional\Operator;

use Rx\Functional\FunctionalTestCase;
use Rx\Observable;

class StartTest extends FunctionalTestCase
{
    /**
     * @test
     */
    public function start_action()
    {
        $done = false;

        $results = $this->scheduler->startWithCreate(function () use (&$done) {
            return Observable::start(function () use (&$done) {
                $done = true;
            }, $this->scheduler);
        });

        $this->assertMessages(
            [
                onNext(200, null),
                onCompleted(200)
            ],
            $results->getMessages()
        );

        $this->assertTrue($done);
    }

    /**
     * @test
     */
    public function start_action_number()
    {
        $results = $this->scheduler->startWithCreate(function () {
            return Observable::start(function () {
                return 1;
            }, $this->scheduler);
        });

        $this->assertMessages(
            [
                onNext(200, 1),
                onCompleted(200)
            ],
            $results->getMessages()
        );
    }

    /**
     * @test
     */
    public function start_with_error()
    {
        $error   = new \Exception();
        $results = $this->scheduler->startWithCreate(function () use ($error) {
            return Observable::start(function () use ($error) {
                throw $error;
            }, $this->scheduler);
        });

        $this->assertMessages(
            [
                onError(200, $error)
            ],
            $results->getMessages()
        );
    }
}
