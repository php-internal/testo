<?php

declare(strict_types=1);

namespace Testo\Suite;

use Testo\Config\SuiteConfig;
use Testo\Finder\Finder;
use Testo\Interceptor\CaseLocatorInterceptor;
use Testo\Interceptor\FileLocatorInterceptor;
use Testo\Interceptor\Implementation\FilePostfixTestLocatorInterceptor;
use Testo\Interceptor\InterceptorProvider;
use Testo\Interceptor\Internal\Pipeline;
use Testo\Module\Tokenizer\DefinitionLocator;
use Testo\Module\Tokenizer\FileLocator;
use Testo\Module\Tokenizer\Reflection\FileDefinitions;
use Testo\Module\Tokenizer\Reflection\TokenizedFile;
use Testo\Suite\Dto\CasesCollection;
use Testo\Suite\Dto\SuiteInfo;
use Testo\Test\Dto\CaseDefinition;

/**
 * Test suite collection and producer of SuiteInfo.
 * Caches SuiteInfo instances.
 */
final class SuiteCollector
{
    /** @var array<string, SuiteInfo> */
    private array $suites = [];

    public function __construct(
        // private readonly ClassLoader $classLoader,
        private readonly InterceptorProvider $interceptorProvider,
    ) {}

    public function get(string $name): ?SuiteInfo
    {
        return $this->suites[$name] ?? null;
    }

    public function getOrCreate(SuiteConfig $config): SuiteInfo
    {
        return $this->suites[$config->name] ??= $this->createInfo($config);
    }

    private function createInfo(SuiteConfig $config): SuiteInfo
    {
        $files = $this->getFilesIterator($config);
        $definitions = $this->getCaseDefinitions($config, $files);

        $result = [];
        foreach ($definitions as $definition) {
            # Skip empty test cases
            if ($definition->tests === []) {
                continue;
            }

            $result[] = $definition;
        }

        return new SuiteInfo(
            name: $config->name,
        );
    }

    /**
     * Locate test files based on the suite configuration and {@see FileLocatorInterceptor} interceptors.
     *
     * @return iterable<TokenizedFile>
     */
    private function getFilesIterator(SuiteConfig $config): iterable
    {
        $locator = new FileLocator(new Finder($config->location));

        # Prepare interceptors pipeline
        $interceptors = $this->interceptorProvider->fromClasses(FileLocatorInterceptor::class);

        /**
         * @see FileLocatorInterceptor::locateFile()
         * @var callable(TokenizedFile): (null|bool) $pipeline
         */
        $pipeline = Pipeline::prepare(...$interceptors)
            ->with(static fn(TokenizedFile $file): ?bool => null, 'locateFile');

        foreach ($locator->getIterator() as $fileReflection) {
            $match = $pipeline($fileReflection);

            if ($match === true) {
                yield $fileReflection;
            }
        }
    }

    /**
     * Fetch test case definitions from the given files using {@see CaseLocatorInterceptor} interceptors.
     *
     * @param iterable<TokenizedFile> $files
     * @return list<CaseDefinition>
     */
    private function getCaseDefinitions(SuiteConfig $config, iterable $files): array
    {
        $cases = [];
        # Prepare interceptors pipeline
        $interceptors = $this->interceptorProvider->fromClasses(CaseLocatorInterceptor::class);

        /**
         * @see CaseLocatorInterceptor::locateTestCases()
         * @var callable(FileDefinitions): CasesCollection $pipeline
         */
        $pipeline = Pipeline::prepare(...$interceptors)
            ->with(
                static fn(FileDefinitions $definitions): CasesCollection => $definitions->cases,
                'locateTestCases',
            );

        foreach ($files as $file) {
            $fileDef = new FileDefinitions($file);
            $result = $pipeline($fileDef);

            $cases = \array_merge($cases, $result->getCases());
        }

        return $cases;
    }
}
