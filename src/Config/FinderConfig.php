<?php

declare(strict_types=1);

namespace Testo\Config;

use Testo\Common\Path;

/**
 * File system scope configuration.
 */
final class FinderConfig
{
    /**
     * @var non-empty-string[]
     * @readonly
     */
    public array $includeDirs = [];

    /**
     * @var non-empty-string[]
     * @readonly
     */
    public array $excludeDirs = [];

    /**
     * @var non-empty-string[]
     * @readonly
     */
    public array $includeFiles = [];

    /**
     * @var non-empty-string[]
     * @readonly
     */
    public array $excludeFiles = [];

    /**
     * @param iterable<non-empty-string|Path> $include Include directories or files to the scope
     * @param iterable<non-empty-string|Path> $exclude Exclude directories or files from the scope
     *
     * @note Glob and regex patterns are not supported
     */
    public function __construct(
        iterable $include = [],
        iterable $exclude = [],
    ) {
        foreach ($include as $dir) {
            $this->include($dir);
        }

        foreach ($exclude as $dir) {
            $this->exclude($dir);
        }
    }

    /**
     * Include directory or file to the scope
     *
     * @param non-empty-string|Path $path
     */
    public function include(string|Path $path): self
    {
        $path = Path::create($path);
        $path->exists() or throw new \InvalidArgumentException("File or directory not found: $path");
        $path->isDir() and $this->includeDirs[] = (string) $path->absolute();
        $path->isFile() and $this->includeFiles[] = (string) $path->absolute();
        return $this;
    }

    /**
     * Exclude a directory or a file from the scope
     *
     * @param non-empty-string|Path $path
     */
    public function exclude(string|Path $path): self
    {
        $path = Path::create($path);
        $path->exists() or throw new \InvalidArgumentException("File or directory not found: $path");
        $path->isDir() and $this->excludeDirs[] = (string) $path->absolute();
        $path->isFile() and $this->excludeFiles[] = (string) $path->absolute();
        return $this;
    }
}
