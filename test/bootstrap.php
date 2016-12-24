<?php

/*
 * This file is part of RxPHP.
 *
 * (c) Alexander <iam.asm89@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

if (file_exists($file = __DIR__ . '/../vendor/autoload.php')) {
    $loader = require $file;
    $loader->add('Rx', __DIR__);
    $loader->addPsr4('CustomOperatorTest\\Rx\\Operator\\', __DIR__ . '/CustomOperatorTest');
    require_once __DIR__ . '/helper-functions.php';
    require_once __DIR__ . '/../vendor/async-interop/event-loop/test/DummyDriver.php';
} else {
    throw new RuntimeException('Install dependencies to run test suite.');
}

/**
 * The default scheduler is the EventLoopScheduler, which is asynchronous.
 * For testing we need to block at `subscribe`, so we need to switch the default to the ImmediateScheduler.
 */
\Rx\Scheduler::setDefault(new \Rx\Scheduler\ImmediateScheduler());