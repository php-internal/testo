<?php

declare(strict_types=1);

namespace Testo\Test\Dto;

use Testo\Common\CloneWith;
use Testo\Test\Definition\CaseDefinition;

/**
 * Information about run test case.
 */
final class CaseInfo
{
    use CloneWith;

    public function __construct(
        public readonly CaseDefinition $definition = new CaseDefinition(),
        /**
         * Test Case class instance if class is defined, null otherwise.
         */
        public readonly ?object $instance = null,
    ) {}

    public function withInstance(?object $instance): self
    {
        /** @see self::$instance */
        return $this->cloneWith('instance', $instance);
    }
}
