<?php

declare(strict_types=1);

namespace Testo\Module\Tokenizer\Reflection;

use Testo\Finder\Path;
use Testo\Module\Tokenizer\Exception\ReflectionException;

/**
 * ReflectionInvocation used to represent function or static method call found by ReflectionFile.
 * This reflection is very useful for static analysis and mainly used in Translator component to
 * index translation function usages.
 */
final class TokenizedInvocation
{
    /**
     * New call reflection.
     *
     * @param class-string $class
     * @param TokenizedArgument[] $arguments
     * @param int $level Was a function used inside another function call?
     */
    public function __construct(
        /**
         * Function usage filename.
         */
        public readonly Path $filename,
        /**
         * Function usage line.
         */
        public readonly int $line,
        /**
         * Parent class.
         * @var class-string|''
         */
        public readonly string $class,
        /**
         * Method operator (:: or ->).
         * @var '::'|'->'|''
         */
        public readonly string $operator,
        /**
         * Function or method name.
         * @var non-empty-string
         */
        public readonly string $name,
        /**
         * All parsed function arguments.
         *
         * @var TokenizedArgument[]
         */
        public readonly array $arguments,
        /**
         * Function usage src.
         */
        public readonly string $source,
        /**
         * Invoking level.
         */
        public readonly int $level,
    ) {}

    /**
     * Call made by class method.
     */
    public function isMethod(): bool
    {
        return !empty($this->class);
    }

    /**
     * Get call argument by it position.
     */
    public function getArgument(int $index): TokenizedArgument
    {
        if (!isset($this->arguments[$index])) {
            throw new ReflectionException(\sprintf("No such argument with index '%d'", $index));
        }

        return $this->arguments[$index];
    }
}
