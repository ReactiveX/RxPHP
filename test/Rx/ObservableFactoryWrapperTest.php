<?php

declare(strict_types = 1);

namespace Rx;

use React\Promise\PromiseInterface;
use function React\Promise\resolve;

class ObservableFactoryWrapperTest extends TestCase
{
    public function testPromiseIsConvertedToObservable()
    {
        $afw = new ObservableFactoryWrapper(static function (): PromiseInterface {
            return resolve(true);
        });
        $true = null;
        $afw()->subscribe(function ($v) use (&$true) {
            $true = $v;
        });

        self::assertTrue($true);
    }

    public function testObservable()
    {
        $afw = new ObservableFactoryWrapper(static function (): Observable {
            return Observable::fromArray([true], Scheduler::getImmediate());
        });
        $true = null;
        $afw()->subscribe(function ($v) use (&$true) {
            $true = $v;
        });

        self::assertTrue($true);
    }

    public function testNotAnObservableOrPromise()
    {
        self::expectException(\Exception::class);
        self::expectExceptionMessageMatches('/You must return an Observable or Promise in/');

        $afw = new ObservableFactoryWrapper(static function (): bool {
            return true;
        });
        $afw();
    }
}
