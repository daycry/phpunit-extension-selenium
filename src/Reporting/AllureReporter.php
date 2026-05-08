<?php

declare(strict_types=1);

namespace Daycry\PHPUnit\Selenium\Reporting;

use Daycry\PHPUnit\Selenium\Config\ReportingConfig;
use Qameta\Allure\Allure;
use Throwable;

/**
 * Thin façade over allure-framework/allure-phpunit. Falls back to a no-op when
 * the optional dependency is not installed, so the library remains usable
 * without an Allure runtime.
 */
final readonly class AllureReporter
{
    private bool $available;

    public function __construct(public ReportingConfig $config)
    {
        $this->available = $this->config->allure
            && class_exists(Allure::class);
    }

    public function isAvailable(): bool
    {
        return $this->available;
    }

    /**
     * Attach a binary file to the current step/test.
     */
    public function attachFile(string $name, string $path, string $mimeType = 'image/png'): void
    {
        if (! $this->available || ! is_file($path)) {
            return;
        }

        try {
            Allure::attachment($name, file_get_contents($path) ?: '', $mimeType);
        } catch (Throwable) {
            // Best-effort reporting; never fail the test because Allure attach failed.
        }
    }

    /**
     * Attach a string payload (capabilities JSON, console log, etc.).
     */
    public function attachText(string $name, string $payload, string $mimeType = 'text/plain'): void
    {
        if (! $this->available) {
            return;
        }

        try {
            Allure::attachment($name, $payload, $mimeType);
        } catch (Throwable) {
            // ignore
        }
    }
}
