<?php

declare(strict_types=1);

namespace Testo\Module\Tokenizer;

use Testo\Module\Tokenizer\Exception\LocatorException;
use Testo\Module\Tokenizer\Reflection\TokenizedFile;

/**
 * Extracts PHP definitions from a tokenized file.
 *
 * Exposes built-in PHP reflections for classes, interfaces, enums, traits, and functions from a given file.
 */
final class DefinitionLocator
{
    /**
     * Get all class reflections defined in the file.
     *
     * @return array<class-string, \ReflectionClass>
     */
    public static function getClasses(TokenizedFile $file): array
    {
        $classes = [];
        foreach ($file->getClasses() as $class) {
            try {
                $classes[$class] = self::classReflection($class);
            } catch (LocatorException $e) {
                // if ($file->isDebug()) {
                //     throw $e;
                // }

                //Ignoring
                continue;
            }
        }

        return $classes;
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
    private static function classReflection(string $class): \ReflectionClass
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
    private function enumReflection(string $enum): \ReflectionEnum
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
