<?php

declare(strict_types=1);

namespace Testo\Interceptor\Implementation;

use Testo\Interceptor\FileLocatorInterceptor;
use Testo\Module\Tokenizer\Reflection\ReflectionFile;

/**
 * Accepts files with the postfix "Test".
 *
 * E.g. "MyClassTest.php" will be accepted, while "MyClass.php" will not.
 */
final class FilePostfixTestLocatorInterceptor implements FileLocatorInterceptor
{
    public function locateFile(ReflectionFile $file, callable $next): ?bool
    {
        return \str_ends_with($file->path->stem(), 'Test') ? true : $next($file);
    }
}
