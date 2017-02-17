<?php

namespace Rx\Functional;

use Rx\Testing\Subscription;

class MarbleTest extends FunctionalTestCase
{
    public function testColdMarble()
    {
        $c = $this->createCold('----1----3---|');

        $result = $this->scheduler->startWithCreate(function () use ($c) {
            return $c;
        });

        $this->assertMessages([
            onNext(240, '1'),
            onNext(290, '3'),
            onCompleted(330)
        ], $result->getMessages());
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
    }

    public function testColdMarbleWithEqualMessages()
    {
        $marbles1 = '----1-^--a--b---|   ';
        $marbles2 = '    1-^--a--b---|---';

        $this->assertMessages(
            $this->convertMarblesToMessages($marbles1),
            $this->convertMarblesToMessages($marbles2)
        );
    }

    public function testMessageConversion()
    {
        $messages = [
            onNext(230, 'a'),
            onNext(260, 'b'),
            onCompleted(300)
        ];

        $this->assertEquals('---a--b---|', $this->convertMessagesToMarbles($messages));
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

    public function testMarbleValues()
    {
        $marbles = '--a--b--c--|';
        $values = [
            'a' => 42,
            'b' => 'xyz',
            'c' => [1, 2, 3],
        ];

        $messages = $this->convertMarblesToMessages($marbles, $values);

        $this->assertMessages([
            onNext(20, 42),
            onNext(50, 'xyz'),
            onNext(80, [1, 2, 3]),
            onCompleted(110)
        ], $messages);
    }

    public function testMarbleValuesDontMatch()
    {
        $marbles = '--a--b--c--|';
        $values = [
            'a' => 42,
            'b' => 'xyz',
            'c' => [1, 2, 3],
        ];

        $messages = $this->convertMarblesToMessages($marbles, $values);

        $this->assertMessagesNotEqual([
            onNext(20, 42),
            onNext(50, 'xyz'),
            onNext(80, [1, 5, 3]), // wrong
            onCompleted(110)
        ], $messages);
    }

    public function testMarbleWithMissingValues()
    {
        $marbles = '--a--b--c--|';
        $values = [
            'a' => 42,
        ];

        $messages = $this->convertMarblesToMessages($marbles, $values);

        $this->assertMessages([
            onNext(20, 42),
            onNext(50, 'b'),
            onNext(80, 'c'),
            onCompleted(110)
        ], $messages);
    }

    public function testSubscriptions()
    {
        $marbles = '--^-----!---^!--';

        $subscriptions = $this->convertMarblesToSubscriptions($marbles, 200);
        $this->assertSubscriptions([
            new Subscription(220, 280),
            new Subscription(320, 330),
        ], $subscriptions);
    }

    public function testSubscriptionsMissingUnsubscribeMarker()
    {
        $marbles = '--^--';

        $subscriptions = $this->convertMarblesToSubscriptions($marbles);
        $this->assertSubscriptions([
            new Subscription(20),
        ], $subscriptions);
    }

    /**
     * @expectedException \Rx\MarbleDiagramError
     */
    public function testSubscriptionsInvalidMarkers()
    {
        $marbles = '--^--a--!-';
        $this->convertMarblesToSubscriptions($marbles);
    }

    /**
     * @expectedException \Rx\MarbleDiagramError
     */
    public function testSubscriptionsMultipleSubscribeMarkers()
    {
        $marbles = '--^-^---!-';
        $this->convertMarblesToSubscriptions($marbles);
    }

    /**
     * @expectedException \Rx\MarbleDiagramError
     */
    public function testSubscriptionsMultipleUnsubscribeMarkers()
    {
        $marbles = '--^---!-!-';
        $this->convertMarblesToSubscriptions($marbles);
    }
}