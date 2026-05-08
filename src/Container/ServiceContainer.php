<?php

declare(strict_types=1);

namespace Daycry\PHPUnit\Selenium\Container;

use Closure;
use Daycry\PHPUnit\Selenium\Container\Exception\NotFoundException;
use Psr\Container\ContainerInterface;

final class ServiceContainer implements ContainerInterface
{
    /** @var array<string, Closure(self): mixed> */
    private array $factories = [];

    /** @var array<string, mixed> */
    private array $resolved = [];

    /**
     * @template T
     * @param class-string<T>|string $id
     * @param Closure(self): T $factory
     */
    public function set(string $id, Closure $factory): void
    {
        $this->factories[$id] = $factory;
        unset($this->resolved[$id]);
    }

    public function instance(string $id, mixed $instance): void
    {
        $this->resolved[$id] = $instance;
        unset($this->factories[$id]);
    }

    public function has(string $id): bool
    {
        return isset($this->factories[$id]) || \array_key_exists($id, $this->resolved);
    }

    public function get(string $id): mixed
    {
        if (\array_key_exists($id, $this->resolved)) {
            return $this->resolved[$id];
        }

        if (! isset($this->factories[$id])) {
            throw new NotFoundException(\sprintf('Service "%s" is not registered.', $id));
        }

        $this->resolved[$id] = ($this->factories[$id])($this);

        return $this->resolved[$id];
    }
}
