<?php

declare(strict_types=1);

namespace Testo\Render\Terminal;

/**
 * Symbols for test status representation in dots mode.
 */
enum DotSymbol: string
{
    case Passed = '.';
    case Failed = 'F';
    case Skipped = '-';
    case Risky = 'R';
    case Error = 'E';
}
