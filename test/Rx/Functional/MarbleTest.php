<?php

namespace Rx\Functional;

class MarbleTest extends FunctionalTestCase
{
    public function testColdMarble()
    {
        $c = $this->createCold('----1----3---|');
        $h = $this->createHot('--^--a--b---|');

        $result = $this->scheduler->startWithCreate(function () use ($c) {
            return $c;
        });

        $this->assertMessages([
            onNext(240, '1'),
            onNext(290, '3'),
            onCompleted(330)
        ], $result->getMessages());

        var_dump($result->getMessages());
    }

    public function testHotMarble()
    {
        $h = $this->createHot('-1-^--a--b---|');

        $result = $this->scheduler->startWithCreate(function () use ($h) {
            return $h;
        });

        $this->assertMessages([
            onNext(230, 'a'),
            onNext(260, 'b'),
            onCompleted(300)
        ], $result->getMessages());

        var_dump($result->getMessages());
    }

    public function testMessageConversion()
    {
        $messages = [
            onNext(230, 'a'),
            onNext(260, 'b'),
            onCompleted(300)
        ];

        $this->assertEquals('--a--b---|', $this->convertMessagesToMarbles($messages));
    }

    public function testSomethingElse()
    {
        $cold     = '--1--2--|';
        $expected = '--2--3--|';

        $results = $this->scheduler->startWithCreate(function () use ($cold) {
            return $this->createCold($cold)->map(function ($x) { return $x + 1; });
        });

        $this->assertEquals($expected, $this->convertMessagesToMarbles($results->getMessages()));
    }
}