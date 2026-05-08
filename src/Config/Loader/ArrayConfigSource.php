<?php

declare(strict_types=1);

namespace Daycry\PHPUnit\Selenium\Config\Loader;

final readonly class ArrayConfigSource implements ConfigSource
{
    /**
     * @param array<string, scalar|array<mixed>|null> $values
     */
    public function __construct(
        private array $values,
        private int $priority = 0,
    ) {
    }

    public function priority(): int
    {
        return $this->priority;
    }

    public function load(): array
    {
        return $this->values;
    }
}
