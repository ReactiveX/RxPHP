<?php

/*
 * This file is part of RxPHP.
 *
 * (c) Alexander <iam.asm89@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use React\EventLoop\Factory;
use Rx\Scheduler;

if (file_exists($file = __DIR__.'/../vendor/autoload.php')) {
    $autoload = require_once $file;
    $autoload->addPsr4('Vendor\\Rx\\Operator\\', __DIR__ . '/custom-operator');
} else {
    throw new RuntimeException('Install dependencies to run test suite.');
}

function asString($value) {
    if (is_array($value)) {
        return json_encode($value);
    } elseif (is_bool($value)) {
        return (string)(integer)$value;
    }
    return (string) $value;
}

$createStdoutObserver = function ($prefix = '') {
    return new Rx\Observer\CallbackObserver(
        function ($value) use ($prefix) { echo $prefix . "Next value: " . asString($value) . "\n"; },
        function ($error) use ($prefix) { echo $prefix . "Exception: " . $error->getMessage() . "\n"; },
        function ()       use ($prefix) { echo $prefix . "Complete!\n"; }
    );
};

$stdoutObserver = $createStdoutObserver();

$loop = Factory::create();
Scheduler::setDefaultFactory(function () use ($loop) {
    return new Scheduler\EventLoopScheduler($loop);
});
register_shutdown_function(function () use ($loop) {
    $loop->run();
});
