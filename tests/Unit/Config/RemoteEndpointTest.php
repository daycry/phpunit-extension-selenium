<?php

declare(strict_types=1);

namespace Daycry\PHPUnit\Selenium\Tests\Unit\Config;

use Daycry\PHPUnit\Selenium\Config\RemoteEndpoint;
use Daycry\PHPUnit\Selenium\Exception\ConfigurationException;
use PHPUnit\Framework\TestCase;

final class RemoteEndpointTest extends TestCase
{
    public function testDefaultValuesAreReasonable(): void
    {
        $endpoint = new RemoteEndpoint();

        self::assertSame(RemoteEndpoint::DEFAULT_HOST, $endpoint->host);
        self::assertSame(30_000, $endpoint->connectTimeoutMs);
        self::assertSame(60_000, $endpoint->requestTimeoutMs);
    }

    public function testInvalidHostIsRejected(): void
    {
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage('Invalid Selenium endpoint URL');

        new RemoteEndpoint(host: 'not-a-url');
    }

    public function testNegativeConnectTimeoutIsRejected(): void
    {
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage('connectTimeoutMs');

        new RemoteEndpoint(connectTimeoutMs: -1);
    }

    public function testNegativeRequestTimeoutIsRejected(): void
    {
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage('requestTimeoutMs');

        new RemoteEndpoint(requestTimeoutMs: -1);
    }
}
