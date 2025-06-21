<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/demo',
        __DIR__ . '/docs',
        __DIR__ . '/src',
        __DIR__ . '/test',
    ])
    ->withPhpVersion(80400)
    ->withPhpSets()
    ->withTypeCoverageLevel(0)
    ->withDeadCodeLevel(0)
    ->withCodeQualityLevel(0);
