<?php

declare(strict_types=1);

namespace Testo\Test\Dto;

final class CaseDefinition
{
    public function __construct(
        public readonly ?\ReflectionClass $reflection = null,
        /**
         * List of tests in the case.
         *
         * @var array<non-empty-string, TestDefinition>
         * @note The key must be a function name.
         * @readonly Use {@see defineTest()} to populate this property.
         */
        public array $tests = [],
        public ?string $runner = null,
    ) {}

    public function defineTest(\ReflectionMethod $method): TestDefinition
    {
        return $this->tests[$method->getName()] ??= new TestDefinition($method);
    }
}
