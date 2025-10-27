<?php

namespace Rx\Functional;

use Rx\Exception\Exception;
use Rx\Testing\Subscription;

class MarbleTest extends FunctionalTestCase
{
    public function testColdMarble(): void
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

    public function testHotMarble(): void
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

    public function testColdMarbleWithEqualMessages(): void
    {
        $marbles1 = '----1-^--a--b---|   ';
        $marbles2 = '    1-^--a--b---|---';

        $this->assertMessages(
            $this->convertMarblesToMessages($marbles1),
            $this->convertMarblesToMessages($marbles2)
        );
    }

    public function testMessageConversion(): void
    {
        $messages = [
            onNext(230, 'a'),
            onNext(260, 'b'),
            onCompleted(300)
        ];

        $this->assertEquals('---a--b---|', $this->convertMessagesToMarbles($messages));
    }

    public function testSomethingElse(): void
    {
        $cold     = '--1--2--|';
        $expected = '--2--3--|';

        $results = $this->scheduler->startWithCreate(function () use ($cold) {
            return $this->createCold($cold)->map(function ($x) { return $x + 1; });
        });

        $this->assertEquals($expected, $this->convertMessagesToMarbles($results->getMessages()));
    }

    public function testMarbleValues(): void
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

    public function testMarbleValuesDontMatch(): void
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

    public function testMarbleWithMissingValues(): void
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

    public function testGroupedMarbleValues(): void
    {
        $marbles = '---(abc)--|';

        $messages = $this->convertMarblesToMessages($marbles);

        $this->assertMessages([
            onNext(30, 'a'),
            onNext(30, 'b'),
            onNext(30, 'c'),
            onCompleted(100)
        ], $messages);
    }

    public function testMultipleGroupedMarbleValues(): void
    {
        $marbles = '--(abc)---(dfa)--|';
        $values = [
            'a' => 42,
        ];

        $messages = $this->convertMarblesToMessages($marbles, $values);

        $this->assertMessages([
            onNext(20, 42),
            onNext(20, 'b'),
            onNext(20, 'c'),
            onNext(100, 'd'),
            onNext(100, 'f'),
            onNext(100, 42),
            onCompleted(170)
        ], $messages);
    }

    public function testGroupedMarkerAndComplete(): void
    {
        $marbles = '--a---b--(c|)';

        $messages = $this->convertMarblesToMessages($marbles);

        $this->assertMessages([
            onNext(20, 'a'),
            onNext(60, 'b'),
            onNext(90, 'c'),
            onCompleted(90)
        ], $messages);
    }

    public function testGroupedMarkerAndError(): void
    {
        $marbles = '--a---(b#)--c--|';

        $messages = $this->convertMarblesToMessages($marbles);

        $this->assertMessages([
            onNext(20, 'a'),
            onNext(60, 'b'),
            onError(60, new \Exception()),
            onNext(120, 'c'),
            onCompleted(150),
        ], $messages);
    }

    public function testSubscriptions(): void
    {
        $marbles = '--^-----!---^!--';

        $subscriptions = $this->convertMarblesToSubscriptions($marbles, 200);
        $this->assertSubscriptions([
            new Subscription(220, 280),
            new Subscription(320, 330),
        ], $subscriptions);
    }

    public function testSubscriptionsMissingUnsubscribeMarker(): void
    {
        $marbles = '--^--';

        $subscriptions = $this->convertMarblesToSubscriptions($marbles);
        $this->assertSubscriptions([
            new Subscription(20),
        ], $subscriptions);
    }

    public function testSubscriptionsGroup(): void
    {
        $marbles = '--(^!)';

        $subscriptions = $this->convertMarblesToSubscriptions($marbles);

        $this->assertSubscriptions([
            new Subscription(20, 20),
        ], $subscriptions);
    }

    /**
     */
    public function testSubscriptionsInvalidMarkers(): void
    {
        $this->expectException(\Rx\MarbleDiagramException::class);
        $marbles = '--^--a--!-';
        $this->convertMarblesToSubscriptions($marbles);
    }

    /**
     */
    public function testSubscriptionsMultipleSubscribeMarkers(): void
    {
        $this->expectException(\Rx\MarbleDiagramException::class);
        $marbles = '--^-^---!-';
        $this->convertMarblesToSubscriptions($marbles);
    }

    /**
     */
    public function testSubscriptionsMultipleUnsubscribeMarkers(): void
    {
        $this->expectException(\Rx\MarbleDiagramException::class);
        $marbles = '--^---!-!-';
        $this->convertMarblesToSubscriptions($marbles);
    }

    public function testMapMarble(): void
    {
        $cold     = '--1--2--|';
        $subs     = '^       !';
        $expected = '--x--y--|';

        $e1 = $this->createCold($cold);

        $r = $e1->map(function ($x) {
            return $x + 1;
        });

        $this->expectObservable($r)->toBe($expected, ['x' => 2, 'y' => 3]);
        $this->expectSubscriptions($e1->getSubscriptions())->toBe($subs);
    }

    public function testMapErrorMarble(): void
    {
        $cold     = '--x--|';
        $subs     = '^ !   ';
        $expected = '--#   ';

        $e1 = $this->createCold($cold, ['x' => 42]);

        $r = $e1->map(function ($x): void {
            throw new \Exception('too bad');
        });

        $this->expectObservable($r)->toBe($expected, [], 'too bad');
        $this->expectSubscriptions($e1->getSubscriptions())->toBe($subs);
    }

    public function testMapDisposeMarble(): void
    {
        $cold     = '--1--2--3--|';
        $unsub    = '      !     ';
        $subs     = '^     !     ';
        $expected = '--x--y-     ';

        $e1 = $this->createCold($cold);

        $r = $e1->map(function ($x) {
            return $x . '!';
        });

        $this->expectObservable($r, $unsub)->toBe($expected, ['x' => '1!', 'y' => '2!']);
        $this->expectSubscriptions($e1->getSubscriptions())->toBe($subs);
    }

    public function testCountMarble(): void
    {
        $cold     = '--a--b--c--|';
        $subs     = '^          !';
        $expected = '-----------(x|)';

        $e1 = $this->createCold($cold);

        $this->expectObservable($e1->count())->toBe($expected, ['x' => 3]);
        $this->expectSubscriptions($e1->getSubscriptions())->toBe($subs);
    }
}
