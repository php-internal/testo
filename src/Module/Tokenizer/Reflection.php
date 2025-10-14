<?php

declare(strict_types=1);

namespace Testo\Module\Tokenizer;

/**
 * Reflection utilities.
 */
final class Reflection
{
    /**
     * Fetch all attributes for a given class.
     *
     * @param class-string $class
     * @param bool $includeParents Whether to include attributes from parent classes.
     * @param bool $includeTraits Whether to include attributes from traits.
     * @param class-string|null $attributeClass If provided, only attributes of this class will be returned.
     * @param int $flags Flags to pass to {@see ReflectionClass::getAttributes()}.
     *
     * @return \ReflectionAttribute[]
     */
    public static function fetchClassAttributes(
        string $class,
        bool $includeParents = true,
        bool $includeTraits = true,
        ?string $attributeClass = null,
        int $flags = 0,
    ): array {
        $attributes = [];

        do {
            $reflection = new \ReflectionClass($class);
            $attributes = \array_merge(
                $attributes,
                $reflection->getAttributes($attributeClass, $flags),
            );

            if ($includeTraits) {
                foreach (self::fetchTraits($class, includeParents: false) as $trait) {
                    $traitReflection = new \ReflectionClass($trait);
                    $attributes = \array_merge(
                        $attributes,
                        $traitReflection->getAttributes($attributeClass, $flags),
                    );
                }
            }

            $class = $includeParents ? $reflection->getParentClass()?->getName() : null;
        } while ($class !== null);

        return $attributes;
    }

    /**
     * Get every class trait (including traits used in parents).
     *
     * @param class-string $class
     * @param bool $includeParents Whether to include traits from parent classes.
     *
     * @return non-empty-string[]
     */
    public static function fetchTraits(
        string $class,
        bool $includeParents = true,
    ): array {
        $traits = [];

        do {
            $traits = \array_merge(\class_uses($class), $traits);
            $class = \get_parent_class($class);
        } while ($includeParents && $class !== false);

        //Traits from traits
        foreach (\array_flip($traits) as $trait) {
            $traits = \array_merge(\class_uses($trait), $traits);
        }

        return \array_unique($traits);
    }
}
