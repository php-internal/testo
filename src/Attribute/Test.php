<?php

declare(strict_types=1);

namespace Testo\Attribute;

/**
 * Marks a method or a function as a test.
 */
#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::TARGET_FUNCTION)]
final class Test {}
