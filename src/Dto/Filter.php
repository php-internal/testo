<?php

declare(strict_types=1);

namespace Testo\Dto;

use Testo\Internal\CloneWith;

/**
 * Filter tests by various criteria.
 *
 * todo: Implement filtering logic.
 */
final class Filter
{
    use CloneWith;

    private function __construct(
        /**
         * @var list<non-empty-string> Names of the test suites to filter by.
         */
        public readonly array $testSuites = [],
    ) {}

    public static function new(): self
    {
        return new self();
    }

    /**
     * Filter tests by Suite names.
     *
     * @param non-empty-string ...$names Names of the test suites to filter by.
     *
     * @return self A new instance of Filter with the specified test names.
     */
    public function withTestSuites(string ...$names): self
    {
        return $this->with('testSuites', \array_unique(\array_merge($this->testSuites, $names)));
    }

    public function withTestCases($name): self
    {
        // TODO
        return $this;
    }
}
