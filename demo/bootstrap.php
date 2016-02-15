<?php

/*
 * This file is part of RxPHP.
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

function asString($value) {
    if (is_array($value)) {
        return json_encode($value);
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
