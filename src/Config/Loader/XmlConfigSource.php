<?php

declare(strict_types=1);

namespace Daycry\PHPUnit\Selenium\Config\Loader;

use PHPUnit\Runner\Extension\ParameterCollection;

final readonly class XmlConfigSource implements ConfigSource
{
    public const PRIORITY = 50;

    private const KEYS = [
        'host',
        'options',
        'browser-name',
        'browser-version',
        'platform-name',
        'page-load-strategy',
        'user-agent',
        'accept-insecure-certs',
        'profile',
        'timeout-page-load-ms',
        'timeout-script-ms',
        'timeout-implicit-ms',
        'timeout-explicit-ms',
        'retry-max-attempts',
        'retry-initial-delay-ms',
        'retry-multiplier',
        'screenshot',
        'screenshot-path',
        'screenshot-mode',
        'screenshot-format',
        'allure',
        'report-path',
        'video-enabled',
    ];

    public function __construct(private ParameterCollection $parameters)
    {
    }

    public function priority(): int
    {
        return self::PRIORITY;
    }

    public function load(): array
    {
        $values = [];
        foreach (self::KEYS as $key) {
            if ($this->parameters->has($key)) {
                $values[$key] = $this->parameters->get($key);
            }
        }

        return $values;
    }
}
