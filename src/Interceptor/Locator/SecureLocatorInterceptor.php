<?php

declare(strict_types=1);

namespace Testo\Interceptor\Locator;

use Testo\Interceptor\FileLocatorInterceptor;
use Testo\Module\Tokenizer\Reflection\TokenizedFile;

/**
 * Interceptor that skips files with {@see include()}, {@see include_once()}, {@see require()},
 * or {@see require_once()} statements.
 */
final class SecureLocatorInterceptor implements FileLocatorInterceptor
{
    public function locateFile(TokenizedFile $file, callable $next): ?bool
    {
        return $file->hasIncludes ? false : $next($file);
    }
}
