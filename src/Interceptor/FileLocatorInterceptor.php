<?php

declare(strict_types=1);

namespace Testo\Interceptor;

use Testo\Interceptor\Internal\InterceptorMarker;
use Testo\Module\Tokenizer\Reflection\ReflectionFile;

/**
 * Intercept locating test files.
 */
interface FileLocatorInterceptor extends InterceptorMarker
{
    /**
     * Return true if the file might be interesting as a test file.
     *
     * The file is not loaded yet, so the interceptor should not try to use reflection on it.
     * Try to use only the file path, class name, doc comments, function names,
     * or other parsed tokens to determine if the file is interesting or dangerous to load.
     *
     * @param ReflectionFile $file Information about the test to be run.
     * @param callable(ReflectionFile): (null|bool) $next Next interceptor or core logic to run the test.
     * @return null|bool True if the file might be interesting as a test file,
     *         false if dangerous to load, null to other interceptors.
     */
    public function locateFile(ReflectionFile $file, callable $next): ?bool;
}
