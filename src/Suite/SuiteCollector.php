<?php

declare(strict_types=1);

namespace Testo\Suite;

use Testo\Config\SuiteConfig;
use Testo\Dto\Suite\SuiteInfo;
use Testo\Finder\Finder;
use Testo\Interceptor\InterceptorProvider;
use Testo\Interceptor\Internal\Pipeline;
use Testo\Interceptor\LocatorInterceptor;
use Testo\Module\Tokenizer\FileLocator;
use Testo\Module\Tokenizer\Reflection\ReflectionFile;

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

        foreach ($files as $file) {
        }

        return new SuiteInfo(
            name: $config->name,
        );
    }

    private function getFilesIterator(SuiteConfig $config): iterable
    {
        $locator = new FileLocator(new Finder($config->location));

        # Prepare interceptors pipeline
        $interceptors = $this->interceptorProvider->fromClasses(LocatorInterceptor::class);
        /** @see LocatorInterceptor::locateFile() */
        $pipeline = Pipeline::prepare(...$interceptors)
            ->with(static fn(ReflectionFile $file): ?bool => null, 'locateFile');

        foreach ($locator->getIterator() as $fileReflection) {
            $match = $pipeline($fileReflection);

            if ($match === true) {
                yield $fileReflection;
            }
        }
    }
}
