<?php

declare(strict_types=1);

namespace Testo\Config\Internal;

use Testo\Common\Container;
use Testo\Common\Inflector;
use Testo\Config\Internal\Attribute\ConfigAttribute;
use Testo\Config\Internal\Attribute\Env;
use Testo\Config\Internal\Attribute\InputArgument;
use Testo\Config\Internal\Attribute\InputOption;
use Testo\Config\Internal\Attribute\PhpIni;
use Testo\Config\Internal\Attribute\XPath;
use Testo\Config\Internal\Attribute\XPathEmbed;
use Testo\Config\Internal\Attribute\XPathEmbedList;
use Internal\DLoad\Service\Logger;

/**
 * Configuration loader service.
 *
 * Hydrates configuration objects with values from different sources
 * based on their property attributes.
 *
 * @internal
 */
final class ConfigInflector implements Inflector
{
    private \SimpleXMLElement|null $xml = null;

    /**
     * Creates a new configuration loader.
     *
     * @psalm-suppress RiskyTruthyFalsyComparison
     */
    public function __construct(
        private readonly Logger $logger,
        private readonly array $env = [],
        private readonly array $inputArguments = [],
        private readonly array $inputOptions = [],
        ?string $xml = null,
    ) {
        if (\is_string($xml)) {
            // Check SimpleXML extension
            if (!\extension_loaded('simplexml')) {
                $logger->info('SimpleXML extension is not loaded.');
            } else {
                $this->xml = \simplexml_load_string($xml, options: \LIBXML_NOERROR) ?: null;
            }
        }
    }

    /**
     * Hydrates a configuration object with values from the configured sources.
     */
    public function inflect(object $object, Container $container): object
    {
        # Detect configs
        if (!\str_starts_with($object::class, 'Testo\\Config\\')) {
            return $object;
        }

        # Read class properties
        $reflection = new \ReflectionObject($object);
        foreach ($reflection->getProperties() as $property) {
            $attributes = $property->getAttributes(ConfigAttribute::class, \ReflectionAttribute::IS_INSTANCEOF);
            if (\count($attributes) === 0) {
                continue;
            }

            $this->injectValue($container, $object, $property, $attributes);
        }

        return $object;
    }

    /**
     * Injects values into a property based on its configuration attributes.
     *
     * @param list<\ReflectionAttribute<ConfigAttribute>> $attributes
     */
    private function injectValue(
        Container $container,
        object $config,
        \ReflectionProperty $property,
        array $attributes,
    ): void {
        foreach ($attributes as $attribute) {
            try {
                $attribute = $attribute->newInstance();

                /** @var mixed $value */
                $value = match (true) {
                    $attribute instanceof XPath => $this->getXPath($attribute),
                    $attribute instanceof XPathEmbed => $this->getXPathEmbedded($container, $attribute),
                    $attribute instanceof XPathEmbedList => $this->getXPathEmbeddedList($container, $attribute),
                    $attribute instanceof Env => $this->env[$attribute->name] ?? null,
                    $attribute instanceof InputOption => $this->inputOptions[$attribute->name] ?? null,
                    $attribute instanceof InputArgument => $this->inputArguments[$attribute->name] ?? null,
                    $attribute instanceof PhpIni => (static fn(string|false $value): ?string => match ($value) {
                        // Option does not exist or set to null
                        '', false => null,
                        default => $value,
                    })(\ini_get($attribute->option)),
                    default => null,
                };

                if (\in_array($value, [null, []], true)) {
                    continue;
                }

                // Cast value to the property type
                $type = $property->getType();

                /** @var mixed $result */
                $result = match (true) {
                    !$type instanceof \ReflectionNamedType => $value,
                    $type->allowsNull() && $value === '' => null,
                    $type->isBuiltin() => match ($type->getName()) {
                        'int' => (int) $value,
                        'float' => (float) $value,
                        'bool' => \filter_var($value, FILTER_VALIDATE_BOOLEAN),
                        'array' => match (true) {
                            \is_array($value) => $value,
                            \is_string($value) => \explode(',', $value),
                            default => [$value],
                        },
                        default => $value,
                    },
                    \enum_exists($type->getName()) => (static function (mixed $value) use ($type): \UnitEnum {
                        /** @var class-string<\BackedEnum> $class */
                        $class = $type->getName();

                        // Get Enum values type
                        $cases = (new \ReflectionEnum($class))->getCases();

                        // If the value is stringable, convert it to string first
                        $value = (string) $value;

                        // Convert value to the backing type
                        if ($cases[0] instanceof \ReflectionEnumBackedCase) {
                            // Find case by backing value
                            \is_int($cases[0]->getBackingValue()) and $value = (int) $value;
                            return $class::from($value);
                        }

                        // Find case by name
                        $value = \strtolower($value);
                        foreach ($cases as $case) {
                            if (\strtolower($case->getName()) === $value) {
                                return $case->getValue();
                            }
                        }

                        throw new \ValueError(\sprintf(
                            'Invalid enum value `%s` for enum `%s`.',
                            $value,
                            $class,
                        ));
                    })($value),
                    default => $value,
                };

                // todo Validation

                // Set the property value
                $property->setValue($config, $result);
                return;
            } catch (\Throwable $e) {
                $this->logger->exception($e, important: true);
            }
        }
    }

    /**
     * Gets a value from XML using an XPath expression.
     */
    private function getXPath(XPath $attribute): mixed
    {
        $value = $this->xml?->xpath($attribute->path);

        return \is_array($value) && \array_key_exists($attribute->key, $value)
            ? $value[$attribute->key]
            : null;
    }

    /**
     * Gets a single object from XML using an XPath expression.
     */
    private function getXPathEmbedded(Container $container, XPathEmbed $attribute): ?object
    {
        if ($this->xml === null) {
            return null;
        }

        $value = $this->xml->xpath($attribute->path);
        if (!\is_array($value) || empty($value)) {
            return null;
        }

        $xml = $value[0];
        \assert($xml instanceof \SimpleXMLElement);

        // Instantiate
        $item = new $attribute->class();

        $this->withXml($xml)->inflect($item, $container);
        return $item;
    }

    /**
     * Gets a list of objects from XML using an XPath expression.
     */
    private function getXPathEmbeddedList(Container $container, XPathEmbedList $attribute): array
    {
        if ($this->xml === null) {
            return [];
        }

        $result = [];
        $value = $this->xml->xpath($attribute->path);
        \is_array($value) or throw new \Exception(\sprintf('Invalid XPath `%s`', $attribute->path));

        foreach ($value as $xml) {
            \assert($xml instanceof \SimpleXMLElement);

            // Instantiate
            $item = new $attribute->class();

            $this->withXml($xml)->inflect($item, $container);
            $result[] = $item;
        }

        return $result;
    }

    /**
     * Creates a new loader instance with the specified XML element.
     */
    private function withXml(\SimpleXMLElement $xml): self
    {
        $self = clone $this;
        $self->xml = $xml;
        return $self;
    }
}
