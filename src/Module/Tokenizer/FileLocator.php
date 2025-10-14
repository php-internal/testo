<?php

declare(strict_types=1);

namespace Testo\Module\Tokenizer;

use Testo\Finder\Finder;
use Testo\Module\Tokenizer\Exception\LocatorException;
use Testo\Module\Tokenizer\Reflection\ReflectionFile;
use Testo\Module\Tokenizer\Traits\TargetTrait;

/**
 * Base class for Class and Invocation locators.
 * @implements \IteratorAggregate<int, ReflectionFile>
 */
final class FileLocator implements \IteratorAggregate
{
    use TargetTrait;

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

    /**
     * Safely get class reflection, class loading errors will be blocked and reflection will be
     * excluded from analysis.
     *
     * @template T
     * @param class-string<T> $class
     * @return \ReflectionClass<T>
     *
     * @throws LocatorException
     */
    protected function classReflection(string $class): \ReflectionClass
    {
        $loader = static function ($class): void {
            if ($class === LocatorException::class) {
                return;
            }

            throw new LocatorException(\sprintf("Class '%s' can not be loaded", $class));
        };

        //To suspend class dependency exception
        \spl_autoload_register($loader);

        try {
            //In some cases reflection can thrown an exception if class invalid or can not be loaded,
            //we are going to handle such exception and convert it soft exception
            return new \ReflectionClass($class);
        } catch (\Throwable $e) {
            if ($e instanceof LocatorException && $e->getPrevious() != null) {
                $e = $e->getPrevious();
            }

            // if ($this->debug) {
            //     $this->getLogger()->error(
            //         \sprintf('%s: %s in %s:%s', $class, $e->getMessage(), $e->getFile(), $e->getLine()),
            //         ['error' => $e],
            //     );
            // }

            throw new LocatorException($e->getMessage(), (int) $e->getCode(), $e);
        } finally {
            \spl_autoload_unregister($loader);
        }
    }

    /**
     * Safely get enum reflection, class loading errors will be blocked and reflection will be
     * excluded from analysis.
     *
     * @param class-string $enum
     *
     * @throws LocatorException
     */
    protected function enumReflection(string $enum): \ReflectionEnum
    {
        $loader = static function (string $enum): void {
            if ($enum === LocatorException::class) {
                return;
            }

            throw new LocatorException(\sprintf("Enum '%s' can not be loaded", $enum));
        };

        //To suspend class dependency exception
        \spl_autoload_register($loader);

        try {
            //In some enum reflection can thrown an exception if enum invalid or can not be loaded,
            //we are going to handle such exception and convert it soft exception
            return new \ReflectionEnum($enum);
        } catch (\Throwable $e) {
            if ($e instanceof LocatorException && $e->getPrevious() != null) {
                $e = $e->getPrevious();
            }

            // if ($this->debug) {
            //     $this->getLogger()->error(
            //         \sprintf('%s: %s in %s:%s', $enum, $e->getMessage(), $e->getFile(), $e->getLine()),
            //         ['error' => $e],
            //     );
            // }

            throw new LocatorException($e->getMessage(), (int) $e->getCode(), $e);
        } finally {
            \spl_autoload_unregister($loader);
        }
    }
}
