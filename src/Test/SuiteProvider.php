<?php

declare(strict_types=1);

namespace Testo\Test;

use Testo\Common\Filter;
use Testo\Common\CloneWith;
use Testo\Config\ApplicationConfig;
use Testo\Config\SuiteConfig;
use Testo\Test\Dto\SuiteInfo;

/**
 * Provides test suites.
 */
final class SuiteProvider
{
    use CloneWith;

    /** @var list<SuiteConfig> */
    private readonly array $configs;

    public function __construct(
        ApplicationConfig $applicationConfig,
        private readonly SuiteCollector $collector,
    ) {
        $this->configs = $applicationConfig->suites;
    }

    /**
     * @psalm-immutable
     */
    public function withFilter(Filter $filter): self
    {
        # Apply suite name filter if exists
        if ($filter->testSuites === []) {
            return $this;
        }

        $suites = \array_filter(
            $this->configs,
            static fn(SuiteConfig $suite) => \in_array($suite->name, $filter->testSuites, true),
        );

        /** @see self::$suites */
        return $this->cloneWith('suites', $suites);
    }

    /**
     * Gets test suite definitions with applied filter.
     *
     * @return array<SuiteInfo>
     */
    public function getSuites(): array
    {
        $result = [];
        foreach ($this->configs as $config) {
            $result[] = $this->collector->getOrCreate($config);
        }

        return $result;
    }
}
