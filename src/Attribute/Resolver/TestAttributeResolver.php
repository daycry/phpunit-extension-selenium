<?php

declare(strict_types=1);

namespace Daycry\PHPUnit\Selenium\Attribute\Resolver;

use Daycry\PHPUnit\Selenium\Attributes\UseSelenium;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

/**
 * Discovers `#[UseSelenium]` attributes on a test class hierarchy + method.
 *
 * Walks parents-first so a base test class can declare defaults that subclasses
 * (and finally the method itself) can override. Repeatable attributes are kept
 * in declaration order, and results are cached per `class::method` to avoid
 * paying reflection cost on every data-provider iteration.
 */
final class TestAttributeResolver
{
    /** @var array<string, ResolvedAttributes|null> */
    private array $cache = [];

    /**
     * @param class-string $className
     */
    public function resolve(string $className, string $methodName): ?ResolvedAttributes
    {
        $key = $className . '::' . $methodName;
        if (\array_key_exists($key, $this->cache)) {
            return $this->cache[$key];
        }

        try {
            $method = new ReflectionMethod($className, $methodName);
        } catch (ReflectionException) {
            return $this->cache[$key] = null;
        }

        $chain = [];

        foreach ($this->classHierarchy($className) as $class) {
            foreach ($class->getAttributes(UseSelenium::class) as $attribute) {
                $chain[] = $attribute->newInstance();
            }
        }

        foreach ($method->getAttributes(UseSelenium::class) as $attribute) {
            $chain[] = $attribute->newInstance();
        }

        if ($chain === []) {
            return $this->cache[$key] = null;
        }

        return $this->cache[$key] = ResolvedAttributes::merge($chain);
    }

    /**
     * Resolve from class only, without a specific method (used at boot time for
     * class-level defaults).
     *
     * @param class-string $className
     */
    public function resolveClass(string $className): ?ResolvedAttributes
    {
        $chain = [];

        foreach ($this->classHierarchy($className) as $class) {
            foreach ($class->getAttributes(UseSelenium::class) as $attribute) {
                $chain[] = $attribute->newInstance();
            }
        }

        return $chain === [] ? null : ResolvedAttributes::merge($chain);
    }

    /**
     * @param class-string $className
     *
     * @return list<ReflectionClass<object>>
     */
    private function classHierarchy(string $className): array
    {
        if (! class_exists($className) && ! interface_exists($className) && ! trait_exists($className)) {
            return [];
        }

        $reflection = new ReflectionClass($className);

        $chain = [];
        $cursor = $reflection;
        while ($cursor !== false) {
            $chain[] = $cursor;
            $cursor = $cursor->getParentClass();
        }

        return array_reverse($chain);
    }
}
