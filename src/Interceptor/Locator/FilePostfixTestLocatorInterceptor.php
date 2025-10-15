<?php

declare(strict_types=1);

namespace Testo\Interceptor\Locator;

use Testo\Interceptor\CaseLocatorInterceptor;
use Testo\Interceptor\FileLocatorInterceptor;
use Testo\Module\Tokenizer\Reflection\FileDefinitions;
use Testo\Module\Tokenizer\Reflection\TokenizedFile;
use Testo\Suite\Dto\CaseDefinitions;

/**
 * Accepts files with the postfix "Test" and fetches test cases from them.
 *
 * E.g. "MyClassTest.php" will be accepted, while "MyClass.php" will not.
 * Then it will look for classes with the postfix "Test" inside the file.
 * If there are no such classes, it tries to find functions and considers them as test cases.
 */
final class FilePostfixTestLocatorInterceptor implements FileLocatorInterceptor, CaseLocatorInterceptor
{
    public function locateFile(TokenizedFile $file, callable $next): ?bool
    {
        return \str_ends_with($file->path->stem(), 'Test') ? true : $next($file);
    }

    /**
     * @inheritDoc
     */
    public function locateTestCases(FileDefinitions $file, callable $next): CaseDefinitions
    {
        foreach ($file->classes as $class) {
            if (!$class->isAbstract() && \str_ends_with($class->getName(), 'Test')) {
                $case = $file->cases->define($class);
                foreach ($class->getMethods() as $method) {
                    if ($method->isPublic() && \str_starts_with($method->getName(), 'test')) {
                        $case->tests->define($method);
                    }
                }
            }
        }

        return $next($file);
    }
}
