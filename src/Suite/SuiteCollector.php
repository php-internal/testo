<?php

declare(strict_types=1);

namespace Testo\Suite;

use Testo\Config\SuiteConfig;
use Testo\Dto\Suite\SuiteInfo;
use Testo\Finder\Finder;

/**
 * Test suite collection and producer of SuiteInfo.
 * Caches SuiteInfo instances.
 */
final class SuiteCollector
{
    /** @var array<string, SuiteInfo> */
    private array $suites = [];

    public function __construct(
        // private readonly ClassLoader $classLoader,
    ) {}

    public function get(string $name): ?SuiteInfo
    {
        return $this->suites[$name] ?? null;
    }

    public function getOrCreate(SuiteConfig $config): SuiteInfo
    {
        return $this->suites[$config->name] ??= $this->createInfo($config);
    }

    private function createInfo(SuiteConfig $config): SuiteInfo
    {
        $finder = new Finder($config->location);

        foreach ($finder->files() as $file) {
            // todo fetch test cases
        }

        return new SuiteInfo(
            name: $config->name,
        );
    }
}
