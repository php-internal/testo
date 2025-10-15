<?php

declare(strict_types=1);

namespace Testo\Test\Dto;

/**
 * Collection of test definitions located in a Test Case
 */
final class TestDefinitions
{
    /**
     * Function definitions with its name as key.
     * @var array<non-empty-string, TestDefinition>
     */
    private array $tests = [];

    public static function fromArray(TestDefinition ...$values): self
    {
        $self = new self();
        $self->tests = \array_values($values);
        return $self;
    }

    public function define(\ReflectionFunctionAbstract $reflection): TestDefinition
    {
        return $this->tests[$reflection->getShortName()] = new TestDefinition($reflection);
    }

    /**
     * Get all located test cases.
     *
     * @return array<non-empty-string, TestDefinition>
     */
    public function getTests(): array
    {
        return $this->tests;
    }
}
