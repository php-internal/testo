<?php

declare(strict_types=1);

namespace Testo\Internal;

/**
 * @internal
 */
trait CloneWith
{
    /**
     * Return a new immutable instance with the specified property value.
     */
    private function with(string $key, mixed $value): static
    {
        # Reflection caching
        static $cache = [];
        $reflection = $cache[static::class] ??= (new \ReflectionClass(static::class));

        $new = $reflection->newInstanceWithoutConstructor();
        $new->{$key} = $value;
        /** @psalm-suppress RawObjectIteration */
        foreach ($this as $k => $v) {
            if ($k === $key) {
                continue;
            }

            $new->{$k} = $v;
        }
        return $new;
    }
}
