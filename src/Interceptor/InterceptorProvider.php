<?php

declare(strict_types=1);

namespace Testo\Interceptor;

use Testo\Attribute\Interceptable;
use Testo\Interceptor\Internal\InterceptorMarker;

final class InterceptorProvider {
    public function __construct(
        private readonly Factory $factory,
    ) {}

    /**
     * Map of interceptable attributes to their interceptors.
     * @var array<class-string<Interceptable>, class-string<InterceptorMarker>>
     */
    private array $map = [];

    public function fromAttribute(Interceptable $attribute): InterceptorMarker
    {
        $class = $attribute::class;
        do {
            if (\array_key_exists($class, $this->map)) {
                return $this->factory->make($this->map[$class], [$attribute]);
            }

            $class = \get_parent_class($attribute);
        } while ($class);

        throw new \RuntimeException("No interceptor found for attribute {$attribute::class}.");
    }
}
