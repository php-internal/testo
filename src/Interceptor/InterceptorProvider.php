<?php

declare(strict_types=1);

namespace Testo\Interceptor;

use Testo\Attribute\Interceptable;
use Testo\Attribute\RetryPolicy;
use Testo\Interceptor\Implementation\RetryPolicyInterceptor;
use Testo\Interceptor\Internal\InterceptorMarker;

final class InterceptorProvider
{
    /**
     * Map of interceptable attributes to their interceptors.
     * @var array<class-string<Interceptable>, class-string<InterceptorMarker>>
     */
    private array $map = [];

    public function __construct(
        private readonly Factory $factory = new Factory(),
    ) {}

    public static function createDefault(): self
    {
        $self = new self();
        $self->map = [
            RetryPolicy::class => RetryPolicyInterceptor::class,
        ];
        return $self;
    }

    /**
     * Get interceptors for the given attributes set filtered by the given class.
     *
     * @template-covariant T of InterceptorMarker
     *
     * @param class-string<T> $class The interceptor class.
     * @param Interceptable ...$attributes Attributes to get interceptors for.
     *
     * @return list<T> Interceptors for the given attributes.
     */
    public function fromAttributes(string $class, Interceptable ...$attributes): array
    {
        $result = [];

        foreach ($attributes as $attribute) {
            # Get alias interceptor
            $iClass = $this->resolveAlias($attribute::class) ?? throw new \RuntimeException(
                \sprintf('No interceptor found for attribute %s.', $attribute::class),
            );

            \is_a($iClass, $class, true) and $result[] = $this->factory->make($iClass, [$attribute]);
        }

        return $result;
    }

    /**
     * Resolve alias interceptor for the given attribute class.
     *
     * @param class-string<Interceptable> $class The attribute class.
     * @return class-string<InterceptorMarker>|null The interceptor class or null if not found.
     */
    private function resolveAlias(string $class): ?string
    {
        do {
            if (\array_key_exists($class, $this->map)) {
                return $this->map[$class];
            }

            $class = \get_parent_class($class);
        } while ($class);

        return null;
    }
}
