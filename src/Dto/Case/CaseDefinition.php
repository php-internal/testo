<?php

declare(strict_types=1);

namespace Testo\Dto\Case;

final class CaseDefinition
{
    public function __construct(
        public readonly ?\ReflectionClass $reflection,
    ) {}
}
