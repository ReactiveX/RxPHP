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
    $loader = require $file;
    $loader->add('Rx', __DIR__);
    $loader->addPsr4('CustomOperatorTest\\Rx\\Operator\\', __DIR__ . '/CustomOperatorTest');
    $loader->addPsr4('CustomOperatorTest\\SubNamespace\\Rx\\Operator\\', __DIR__ . '/CustomOperatorTest/SubNamespace');
    require_once __DIR__ . '/helper-functions.php';
} else {
    throw new RuntimeException('Install dependencies to run test suite.');
}

