<?php

declare(strict_types=1);

namespace Daycry\PHPUnit\Selenium\Storage;

use Daycry\PHPUnit\Selenium\Exception\ConfigurationException;
use Facebook\WebDriver\Remote\RemoteWebDriver;

/**
 * Wrapper over window.localStorage / window.sessionStorage via executeScript.
 */
final readonly class Storage
{
    public function __construct(
        private RemoteWebDriver $driver,
        private string $kind,
    ) {
        if (! \in_array($this->kind, ['localStorage', 'sessionStorage'], true)) {
            throw new ConfigurationException(\sprintf('Unknown storage kind "%s".', $this->kind));
        }
    }

    public function set(string $key, string $value): self
    {
        $this->driver->executeScript(
            \sprintf('window.%s.setItem(arguments[0], arguments[1]);', $this->kind),
            [$key, $value],
        );

        return $this;
    }

    public function get(string $key): ?string
    {
        $result = $this->driver->executeScript(
            \sprintf('return window.%s.getItem(arguments[0]);', $this->kind),
            [$key],
        );

        return \is_string($result) ? $result : null;
    }

    public function remove(string $key): self
    {
        $this->driver->executeScript(
            \sprintf('window.%s.removeItem(arguments[0]);', $this->kind),
            [$key],
        );

        return $this;
    }

    public function clear(): self
    {
        $this->driver->executeScript(\sprintf('window.%s.clear();', $this->kind));

        return $this;
    }
}
