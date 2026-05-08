<?php

declare(strict_types=1);

namespace Daycry\PHPUnit\Selenium\Session;

use Daycry\PHPUnit\Selenium\Config\ResolvedConfig;
use Daycry\PHPUnit\Selenium\Exception\SeleniumException;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Throwable;

final class SeleniumSession
{
    private ?RemoteWebDriver $driver = null;
    private ?Throwable $lastError = null;

    public function __construct(
        public readonly string $testId,
        public readonly ResolvedConfig $config,
        private readonly WebDriverFactoryInterface $factory,
    ) {
    }

    public function start(): RemoteWebDriver
    {
        if ($this->driver instanceof RemoteWebDriver) {
            return $this->driver;
        }

        try {
            $this->driver = $this->factory->create($this->config);
            $this->applyTimeouts($this->driver);
        } catch (Throwable $e) {
            $this->lastError = $e;
            $this->driver = null;
            throw new SeleniumException(
                \sprintf('Failed to start Selenium session for "%s": %s', $this->testId, $e->getMessage()),
                0,
                $e,
            );
        }

        return $this->driver;
    }

    public function isStarted(): bool
    {
        return $this->driver instanceof RemoteWebDriver;
    }

    public function driver(): RemoteWebDriver
    {
        if (!$this->driver instanceof RemoteWebDriver) {
            return $this->start();
        }

        return $this->driver;
    }

    public function lastError(): ?Throwable
    {
        return $this->lastError;
    }

    public function close(): void
    {
        if (!$this->driver instanceof RemoteWebDriver) {
            return;
        }

        try {
            $this->driver->quit();
        } catch (Throwable) {
            // Best-effort cleanup; the underlying remote session may already be gone.
        } finally {
            $this->driver = null;
        }
    }

    private function applyTimeouts(RemoteWebDriver $driver): void
    {
        $manage = $driver->manage();
        $timeouts = $manage->timeouts();

        if ($this->config->timeouts->implicitWaitMs > 0) {
            $timeouts->implicitlyWait((int) ceil($this->config->timeouts->implicitWaitMs / 1000));
        }

        if ($this->config->timeouts->pageLoadMs > 0) {
            $timeouts->pageLoadTimeout((int) ceil($this->config->timeouts->pageLoadMs / 1000));
        }

        if ($this->config->timeouts->scriptMs > 0) {
            $timeouts->setScriptTimeout((int) ceil($this->config->timeouts->scriptMs / 1000));
        }
    }
}
