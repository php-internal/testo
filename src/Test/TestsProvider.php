<?php

declare(strict_types=1);

namespace Testo\Test;

use Testo\Common\Filter;
use Testo\Test\Definition\CaseDefinition;
use Testo\Test\Definition\TestDefinition;

final class TestsProvider
{
    /**
     * @psalm-immutable
     */
    public function withFilter(Filter $filter): self
    {
        return $this;
    }

    /**
     * Gets test definitions with applied filter.
     *
     * @return iterable<TestDefinition>
     */
    public function getTests(): iterable
    {
        yield from [];
    }

    /**
     * Gets test case definitions with applied filter.
     *
     * @return iterable<CaseDefinition>
     */
    public function getCases(): iterable
    {
        yield from [];
    }
}
