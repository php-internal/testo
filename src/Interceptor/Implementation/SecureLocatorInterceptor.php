<?php

declare(strict_types=1);

namespace Testo\Interceptor\Implementation;

use Testo\Interceptor\FileLocatorInterceptor;
use Testo\Module\Tokenizer\Reflection\ReflectionFile;

/**
 * Interceptor that skips files with {@see include()}, {@see include_once()}, {@see require()},
 * or {@see require_once()} statements.
 */
final class SecureLocatorInterceptor implements FileLocatorInterceptor
{
    public function locateFile(ReflectionFile $file, callable $next): ?bool
    {
        return $file->hasIncludes ? false : $next($file);
    }
}
