<?php

declare(strict_types=1);

namespace Testo\Config;

/**
 * Test Suite configuration.
 */
final class ApplicationConfig
{

    public function __construct(
        /**
         * Source code location.
         */
        public readonly ?FinderConfig $src = null,

        /**
         * Specify one or more Test Suites to be executed.
         *
         * @var non-empty-list<SuiteConfig>
         */
        public readonly array $suites = [
            new SuiteConfig(
                name: 'default',
                location: new FinderConfig('tests'),
            ),
        ],

        /**
         * Services bindings configuration.
         */
        public readonly ServicesConfig $services = new ServicesConfig(),
    ) {
        $suites === [] and throw new \InvalidArgumentException('At least one test suite must be defined.');
    }
}
