<?php

declare(strict_types=1);

namespace Daycry\PHPUnit\Selenium\Config\Loader;

interface ConfigSource
{
    /**
     * Lower priority sources are applied first; higher priority overrides them.
     */
    public function priority(): int;

    /**
     * @return array<string, scalar|array<mixed>|null>
     */
    public function load(): array;
}
