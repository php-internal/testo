<?php

declare(strict_types=1);

namespace Testo\Common;

trait AttributedTrait
{
    use CloneWith;

    /** @var array<non-empty-string, mixed> */
    public readonly array $attributes;

    /**
     * @return array<non-empty-string, mixed> Attributes derived from the context.
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * @param non-empty-string $name
     */
    public function getAttribute(string $name, mixed $default = null): mixed
    {
        return $this->attributes[$name] ?? $default;
    }

    /**
     * @param non-empty-string $name
     */
    public function withAttribute(string $name, mixed $value): static
    {
        $attributes = $this->attributes;
        $attributes[$name] = $value;
        return $this->cloneWith('attributes', $attributes);
    }

    /**
     * @param non-empty-string $name
     */
    public function withoutAttribute(string $name): static
    {
        $attributes = $this->attributes;
        unset($attributes[$name]);
        return $this->cloneWith('attributes', $attributes);
    }
}
