<?php

declare(strict_types=1);

namespace Testo\Test\Dto;

use Testo\Common\AttributedTrait;
use Testo\Test\Definition\CaseDefinition;

/**
 * Information about run test case.
 */
final class CaseInfo
{
    use AttributedTrait;

    public readonly string $name;

    /**
     * @param array<non-empty-string, mixed> $attributes
     */
    public function __construct(
        public readonly CaseDefinition $definition = new CaseDefinition(),
        /**
         * Test Case class instance if class is defined, null otherwise.
         */
        public readonly ?object $instance = null,
        array $attributes = [],
    ) {
        $this->name = $definition->getName();
        $this->attributes = $attributes;
    }

    public function withInstance(?object $instance): self
    {
        /** @see self::$instance */
        return $this->cloneWith('instance', $instance);
    }
}
