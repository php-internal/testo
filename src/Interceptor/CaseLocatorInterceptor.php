<?php

declare(strict_types=1);

namespace Testo\Interceptor;

use Testo\Interceptor\Internal\InterceptorMarker;
use Testo\Module\Tokenizer\Reflection\FileDefinitions;
use Testo\Suite\Dto\CasesCollection;

/**
 * Intercept locating test files and test cases.TokenizedFile
 *
 * @extends InterceptorMarker<FileDefinitions, CasesCollection>
 */
interface CaseLocatorInterceptor extends InterceptorMarker
{
    /**
     * Locate test cases in the given file.
     *
     * Class and function reflections are available there.
     *
     * @param FileDefinitions $file File to locate test cases in.
     * @param callable(FileDefinitions): CasesCollection $next Next interceptor or core logic to locate test cases.
     */
    public function locateTestCases(FileDefinitions $file, callable $next): CasesCollection;
}
