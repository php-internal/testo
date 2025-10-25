<?php

declare(strict_types=1);

namespace Testo\Test\Dto;

use Testo\Test\Definition\CaseDefinition;

/**
 * Collection of test cases located in a file.
 */
final class CaseDefinitions
{
    /**
     * Located test cases.
     * @var list<CaseDefinition>
     */
    private array $cases = [];

    public static function fromArray(CaseDefinition ...$values): self
    {
        $self = new self();
        $self->cases = \array_values($values);
        return $self;
    }

    public function define(?\ReflectionClass $reflection): CaseDefinition
    {
        foreach ($this->cases as $case) {
            if ($case->reflection === $reflection) {
                return $case;
            }
        }

        return $this->cases[] = new CaseDefinition($reflection);
    }

    /**
     * Get all located test cases.
     *
     * @return list<CaseDefinition>
     */
    public function getCases(): array
    {
        return $this->cases;
    }
}
