<?php

declare(strict_types=1);

namespace Testo\Dto\Suite;

final class SuiteInfo
{
    public function __construct(
        /** @var non-empty-string */
        public readonly string $name,
    ) {}
}
