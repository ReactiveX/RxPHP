<?php

/*
 * This file is part of Rx.PHP.
 *
 * (c) Alexander <iam.asm89@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

if (file_exists($file = __DIR__.'/../vendor/autoload.php')) {
    $autoload = require_once $file;
} else {
    throw new RuntimeException('Install dependencies to run test suite.');
}

$stdoutObserver = new Rx\Observer\CallbackObserver(
    function ($value) { echo "Next value: " . $value . "\n"; },
    function ($error) { echo "Exception: " . $error->getMessage() . "\n"; },
    function ()       { echo "Complete!\n"; }
);
