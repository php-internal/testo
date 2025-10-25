<?php

declare(strict_types=1);

namespace Testo\Render;

/**
 * Helper utilities for text-based rendering (CLI, TeamCity, logs, etc.).
 *
 * @internal
 */
final class Helper
{
    /**
     * Formats a throwable into a detailed string with class, message, file, line, and stack trace.
     */
    public static function formatThrowable(\Throwable $throwable): string
    {
        $class = $throwable::class;
        $message = $throwable->getMessage();
        $file = $throwable->getFile();
        $line = $throwable->getLine();
        $trace = $throwable->getTraceAsString();

        return "{$class}: {$message}\nFile: {$file}:{$line}\n\nStack trace:\n{$trace}";
    }
}
