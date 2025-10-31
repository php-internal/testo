<?php

declare(strict_types=1);

use Testo\Config\ApplicationConfig;
use Testo\Config\SuiteConfig;

return new ApplicationConfig(
    suites: \array_merge(
        [
            new SuiteConfig(
                name: 'default',
                location: new \Testo\Config\FinderConfig(
                    include: ['tests/Testo'],
                ),
            ),
        ],
        include 'tests/Assert/suites.php',
    ),
);
