<?php

declare(strict_types=1);

namespace Testo\Module\Tokenizer\Reflection;

use Testo\Module\Tokenizer\DefinitionLocator;
use Testo\Suite\Dto\CasesCollection;

final class FileDefinitions
{
    /**
     * Class reflections found in the file.
     * @var array<class-string, \ReflectionClass>
     */
    public readonly array $classes;

    /**
     * Interface reflections found in the file.
     * @var array<class-string, \ReflectionClass>
     */
    public readonly array $interfaces;

    /**
     * Enum reflections found in the file.
     * @var array<class-string, \ReflectionEnum>
     */
    public readonly array $enums;

    /**
     * Function reflections found in the file.
     * @var array<string, \ReflectionFunction>
     */
    public readonly array $functions;

    /**
     * Trait reflections found in the file.
     * @var array<class-string, \ReflectionClass>
     */
    public readonly array $traits;

    public function __construct(
        public readonly TokenizedFile $tokenizedFile,
        public readonly CasesCollection $cases = new CasesCollection(),
    ) {
        $this->classes = DefinitionLocator::getClasses($tokenizedFile);
        // $this->enums = DefinitionLocator::getEnums($tokenizedFile);
        // $this->functions = DefinitionLocator::getFunctions($tokenizedFile);
        // $this->interfaces = DefinitionLocator::getInterfaces($tokenizedFile);
        // $this->traits = DefinitionLocator::getTraits($tokenizedFile);
    }
}
