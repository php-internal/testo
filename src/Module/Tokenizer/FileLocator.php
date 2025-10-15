<?php

declare(strict_types=1);

namespace Testo\Module\Tokenizer;

use Testo\Finder\Finder;
use Testo\Module\Tokenizer\Reflection\TokenizedFile;

/**
 * Locates and tokenizes PHP files within a given FS scope.
 *
 * Reads files discovered by {@see Finder}, tokenizes their contents,
 * and creates {@see TokenizedFile} objects.
 *
 * @implements \IteratorAggregate<int, TokenizedFile>
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
     * @return \Generator<int, TokenizedFile, mixed, void>
     * @throws \Exception
     */
    public function getIterator(): \Generator
    {
        foreach ($this->finder->getIterator() as $file) {
            yield new TokenizedFile($file, (string) $file);
        }
    }
}
