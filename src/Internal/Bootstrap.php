<?php

declare(strict_types=1);

namespace Testo\Internal;

use Testo\Internal\Service\ObjectContainer;

/**
 * Bootstraps the application by configuring the dependency container.
 *
 * Initializes the application container with configuration values and core services.
 * Serves as the entry point for the dependency injection setup.
 *
 * @internal
 */
final class Bootstrap
{
    private function __construct(
        private Container $container,
    ) {}

    /**
     * Creates a new bootstrap instance with the specified container.
     *
     * @param Container $container Dependency injection container
     * @return self Bootstrap instance
     */
    public static function init(Container $container = new ObjectContainer()): self
    {
        return new self($container);
    }

    /**
     * Finalizes the bootstrap process and returns the configured container.
     *
     * @return Container Fully configured dependency container
     */
    public function finish(): Container
    {
        $c = $this->container;
        unset($this->container);

        return $c;
    }

    /**
     * Configures the container with XML configuration and input values.
     *
     * Registers core services and bindings for system architecture, OS detection,
     * and stability settings.
     *
     * @return self Configured bootstrap instance
     * @throws \InvalidArgumentException When config file is not found
     * @throws \RuntimeException When config file cannot be read
     */
    public function withConfig(
    ): self {
        return $this;
    }
}
