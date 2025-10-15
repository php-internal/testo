<?php

declare(strict_types=1);

namespace Testo\Suite;

use Testo\Config\SuiteConfig;
use Testo\Finder\Finder;
use Testo\Interceptor\CaseLocatorInterceptor;
use Testo\Interceptor\FileLocatorInterceptor;
use Testo\Interceptor\InterceptorProvider;
use Testo\Interceptor\Internal\Pipeline;
use Testo\Interceptor\Locator\FilePostfixTestLocatorInterceptor;
use Testo\Interceptor\Locator\TestoAttributesLocatorInterceptor;
use Testo\Module\Tokenizer\FileLocator;
use Testo\Module\Tokenizer\Reflection\FileDefinitions;
use Testo\Module\Tokenizer\Reflection\TokenizedFile;
use Testo\Suite\Dto\CaseDefinitions;
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

        $cases = [];
        foreach ($definitions as $definition) {
            # Skip empty test cases
            if ($definition->tests === []) {
                continue;
            }

            $cases[] = $definition;
        }

        return new SuiteInfo(
            name: $config->name,
            testCases: CaseDefinitions::fromArray(...$cases),
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

        # todo remove:
        // $interceptors[] = new FilePostfixTestLocatorInterceptor();
        $interceptors[] = new TestoAttributesLocatorInterceptor();

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

        // todo remove:
        $interceptors[] = new FilePostfixTestLocatorInterceptor();

        /**
         * @see CaseLocatorInterceptor::locateTestCases()
         * @var callable(FileDefinitions): CaseDefinitions $pipeline
         */
        $pipeline = Pipeline::prepare(...$interceptors)
            ->with(
                static fn(FileDefinitions $definitions): CaseDefinitions => $definitions->cases,
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
