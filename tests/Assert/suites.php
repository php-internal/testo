<?php

declare(strict_types=1);

use Testo\Config\FinderConfig;
use Testo\Config\SuiteConfig;

/**
 * Test suites for Assert component.
 */
return [
    new SuiteConfig(
        name: 'Assert: Feature',
        location: new FinderConfig(
            include: [__DIR__ . '/Feature'],
        ),
    ),
    new SuiteConfig(
        name: 'Assert: Self Testing',
        location: new FinderConfig(
            include: [__DIR__ . '/Self'],
        ),
    ),
];
