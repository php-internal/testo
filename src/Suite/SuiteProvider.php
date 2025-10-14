<?php

declare(strict_types=1);

namespace Testo\Suite;

use Testo\Dto\Filter;
use Testo\Dto\Suite\SuiteInfo;

/**
 * Provides test suites.
 */
final class SuiteProvider
{
    /**
     * @psalm-immutable
     */
    public function withFilter(Filter $filter): self
    {
        return $this;
    }

    /**
     * Gets test suite definitions with applied filter.
     *
     * @return array<SuiteInfo>
     */
    public function getSuites(): array
    {
        return [
            new SuiteInfo(null),
        ];
    }
}
