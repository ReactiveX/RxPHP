<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php84\Rector\Param\ExplicitNullableParamTypeRector;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/demo',
        __DIR__ . '/docs',
        __DIR__ . '/src',
        __DIR__ . '/test',
    ])
    ->withPhpVersion(80400)
    ->withPhpSets()
    ->withTypeCoverageLevel(1)
    ->withDeadCodeLevel(0)
    ->withCodeQualityLevel(0)
    ->withRules([
        ExplicitNullableParamTypeRector::class,
    ]);
