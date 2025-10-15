<?php

declare(strict_types=1);

namespace Testo\Suite\Dto;

use Testo\Test\Dto\CaseDefinition;

/**
 * Collection of test cases located in a file.
 */
final class CasesCollection
{
    /**
     * Located test cases.
     * @var list<CaseDefinition>
     */
    private array $cases = [];

    public function declareCase(?\ReflectionClass $reflection): CaseDefinition
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
