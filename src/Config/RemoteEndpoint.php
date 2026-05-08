<?php

declare(strict_types=1);

namespace Daycry\PHPUnit\Selenium\Config;

use Daycry\PHPUnit\Selenium\Exception\ConfigurationException;

final readonly class RemoteEndpoint
{
    public const DEFAULT_HOST = 'http://localhost:4444/wd/hub';

    public function __construct(
        public string $host = self::DEFAULT_HOST,
        public int $connectTimeoutMs = 30_000,
        public int $requestTimeoutMs = 60_000,
    ) {
        if (filter_var($this->host, FILTER_VALIDATE_URL) === false) {
            throw new ConfigurationException(\sprintf('Invalid Selenium endpoint URL: "%s".', $this->host));
        }

        if ($this->connectTimeoutMs < 0) {
            throw new ConfigurationException(\sprintf('connectTimeoutMs must be >= 0, got %d.', $this->connectTimeoutMs));
        }

        if ($this->requestTimeoutMs < 0) {
            throw new ConfigurationException(\sprintf('requestTimeoutMs must be >= 0, got %d.', $this->requestTimeoutMs));
        }
    }
}
