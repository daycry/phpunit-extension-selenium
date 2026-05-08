<?php

declare(strict_types=1);

namespace Daycry\PHPUnit\Selenium\Browser\Capabilities;

use Daycry\PHPUnit\Selenium\Browser\BrowserType;

final readonly class SafariCapabilities extends BrowserCapabilities
{
    /**
     * @param array<string, scalar|array<mixed>> $extra
     */
    public function __construct(
        public bool $technologyPreview = false,
        ?string $browserVersion = null,
        ?string $platformName = 'mac',
        bool $acceptInsecureCerts = false,
        ?string $pageLoadStrategy = null,
        ?string $userAgent = null,
        array $extra = [],
    ) {
        parent::__construct(
            browser: BrowserType::Safari,
            browserVersion: $browserVersion,
            platformName: $platformName,
            acceptInsecureCerts: $acceptInsecureCerts,
            pageLoadStrategy: $pageLoadStrategy,
            userAgent: $userAgent,
            extra: $extra,
        );
    }
}
