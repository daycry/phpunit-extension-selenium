<?php

declare(strict_types=1);

namespace Daycry\PHPUnit\Selenium\Browser\Capabilities;

use Daycry\PHPUnit\Selenium\Browser\BrowserType;

final readonly class FirefoxCapabilities extends BrowserCapabilities
{
    /**
     * @param list<string> $args
     * @param array<string, scalar|array<mixed>> $prefs
     * @param array<string, scalar|array<mixed>> $extra
     */
    public function __construct(
        public array $args = [],
        public array $prefs = [],
        public ?string $profileDir = null,
        public ?string $binary = null,
        ?string $browserVersion = null,
        ?string $platformName = null,
        bool $acceptInsecureCerts = false,
        ?string $pageLoadStrategy = null,
        ?string $userAgent = null,
        array $extra = [],
    ) {
        parent::__construct(
            browser: BrowserType::Firefox,
            browserVersion: $browserVersion,
            platformName: $platformName,
            acceptInsecureCerts: $acceptInsecureCerts,
            pageLoadStrategy: $pageLoadStrategy,
            userAgent: $userAgent,
            extra: $extra,
        );
    }
}
