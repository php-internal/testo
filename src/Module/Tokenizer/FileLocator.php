<?php

declare(strict_types=1);

namespace Testo\Module\Tokenizer;

use Testo\Finder\Finder;
use Testo\Module\Tokenizer\Reflection\ReflectionFile;

/**
 * Locates and tokenizes PHP files within a given FS scope.
 *
 * Reads files discovered by {@see Finder}, tokenizes their contents,
 * and creates {@see ReflectionFile} objects.
 *
 * @implements \IteratorAggregate<int, ReflectionFile>
 */
final class FileLocator implements \IteratorAggregate
{
    protected readonly Finder $finder;

    public function __construct(
        Finder $finder,
        protected readonly bool $debug = false,
    ) {
        $this->finder = $finder->files();
    }

    /**
     * Available file reflections. Generator.
     *
     * @return \Generator<int, ReflectionFile, mixed, void>
     * @throws \Exception
     */
    public function getIterator(): \Generator
    {
        foreach ($this->finder->getIterator() as $file) {
            yield new ReflectionFile($file, (string) $file);
        }
    }
}
