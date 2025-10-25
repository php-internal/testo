<?php

declare(strict_types=1);

namespace Testo\Render\Terminal;

/**
 * Symbols for test status representation in compact/verbose modes.
 */
enum Symbol: string
{
    case Success = '✓';
    case Failure = '✗';
    case Skipped = '○';
    case Risky = '?';
    case Flaky = '~';
    case Error = 'E';
}
