<?php

declare(strict_types=1);

namespace Daycry\PHPUnit\Selenium\Reporting;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Throwable;

/**
 * Best-effort collector of browser-side logs (console + driver) via the
 * Selenium logging endpoint. Returns an empty list when the underlying
 * driver/browser does not support log retrieval.
 */
final class BrowserLogCollector
{
    /**
     * @return array{browser: list<array<string, mixed>>, driver: list<array<string, mixed>>}
     */
    public function collect(RemoteWebDriver $driver): array
    {
        return [
            'browser' => $this->safeRead($driver, 'browser'),
            'driver' => $this->safeRead($driver, 'driver'),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function safeRead(RemoteWebDriver $driver, string $type): array
    {
        try {
            /** @var list<array<string, mixed>> $logs */
            $logs = $driver->manage()->getLog($type);

            return $logs;
        } catch (Throwable) {
            return [];
        }
    }
}
