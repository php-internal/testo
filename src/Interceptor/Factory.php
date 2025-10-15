<?php

declare(strict_types=1);

namespace Testo\Interceptor;

use Testo\Interceptor\Internal\InterceptorMarker;
use Yiisoft\Injector\Injector;

/**
 * Factory for creating interceptors.
 */
final class Factory
{
    private readonly Injector $injector;

    public function __construct(
        Injector $injector = new Injector(),
    ) {
        $this->injector = $injector->withCacheReflections(true);
    }

    /**
     * Creates an instance of the given class with the given arguments.
     *
     * @template T of InterceptorMarker
     *
     * @param class-string<T> $class The class to create.
     * @param array $arguments The arguments to pass to the constructor.
     *
     * @return T The created instance.
     */
    public function make(string $class, array $arguments = []): InterceptorMarker
    {
        return $this->injector->make($class, $arguments);
    }
}
