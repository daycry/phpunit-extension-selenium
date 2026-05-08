<?php

declare(strict_types=1);

namespace Daycry\PHPUnit\Selenium\Config\Loader;

final class EnvConfigSource implements ConfigSource
{
    public const int PRIORITY = 100;

    private const array MAP = [
        'SELENIUM_HOST' => 'host',
        'SELENIUM_BROWSER' => 'browser-name',
        'SELENIUM_BROWSER_VERSION' => 'browser-version',
        'SELENIUM_PLATFORM' => 'platform-name',
        'SELENIUM_USER_AGENT' => 'user-agent',
        'SELENIUM_PAGE_LOAD_STRATEGY' => 'page-load-strategy',
        'SELENIUM_ACCEPT_INSECURE_CERTS' => 'accept-insecure-certs',
        'SELENIUM_OPTIONS' => 'options',
        'SELENIUM_PROFILE' => 'profile',
        'SELENIUM_TIMEOUT_PAGE_LOAD_MS' => 'timeout-page-load-ms',
        'SELENIUM_TIMEOUT_SCRIPT_MS' => 'timeout-script-ms',
        'SELENIUM_TIMEOUT_IMPLICIT_MS' => 'timeout-implicit-ms',
        'SELENIUM_TIMEOUT_EXPLICIT_MS' => 'timeout-explicit-ms',
        'SELENIUM_RETRY_MAX_ATTEMPTS' => 'retry-max-attempts',
        'SELENIUM_RETRY_INITIAL_DELAY_MS' => 'retry-initial-delay-ms',
        'SELENIUM_RETRY_MULTIPLIER' => 'retry-multiplier',
        'SELENIUM_SCREENSHOT' => 'screenshot',
        'SELENIUM_SCREENSHOT_PATH' => 'screenshot-path',
        'SELENIUM_SCREENSHOT_MODE' => 'screenshot-mode',
        'SELENIUM_SCREENSHOT_FORMAT' => 'screenshot-format',
        'SELENIUM_ALLURE' => 'allure',
        'SELENIUM_REPORT_PATH' => 'report-path',
        'SELENIUM_VIDEO_ENABLED' => 'video-enabled',
    ];

    /** @var callable(string): (string|false) */
    private $reader;

    public function __construct(?callable $reader = null)
    {
        $this->reader = $reader ?? getenv(...);
    }

    public function priority(): int
    {
        return self::PRIORITY;
    }

    public function load(): array
    {
        $values = [];
        foreach (self::MAP as $env => $key) {
            $value = ($this->reader)($env);
            if ($value === false) {
                continue;
            }
            if ($value === '') {
                continue;
            }

            $values[$key] = $value;
        }

        return $values;
    }
}
