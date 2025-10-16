<?php

declare(strict_types=1);

namespace Testo\Assert;

/**
 * Collects assertions.
 */
final class AssertCollector
{
    /**
     * @var list<Record> The history of assertions.
     */
    public array $history = [];
}
