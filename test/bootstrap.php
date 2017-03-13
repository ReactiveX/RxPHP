<?php

declare(strict_types = 1);

/*
 * This file is part of RxPHP.
 *
 * (c) Alexander <iam.asm89@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

if (file_exists($file = __DIR__ . '/../vendor/autoload.php')) {
    require $file;
} else {
    throw new RuntimeException('Install dependencies to run test suite.');
}

/**
 * The default scheduler is the EventLoopScheduler, which is asynchronous.
 * For testing we need to block at `subscribe`, so we need to switch the default to the ImmediateScheduler.
 */
\Rx\Scheduler::setDefaultFactory(function () {
    return new \Rx\Scheduler\ImmediateScheduler();
});

require 'loop-auto-start.php';
