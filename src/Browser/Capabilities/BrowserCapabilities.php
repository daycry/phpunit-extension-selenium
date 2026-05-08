<?php

declare(strict_types=1);

namespace Daycry\PHPUnit\Selenium\Browser\Capabilities;

use Daycry\PHPUnit\Selenium\Browser\BrowserType;

abstract readonly class BrowserCapabilities
{
    /**
     * @param array<string, scalar|array<mixed>> $extra
     */
    public function __construct(
        public BrowserType $browser,
        public ?string $browserVersion = null,
        public ?string $platformName = null,
        public bool $acceptInsecureCerts = false,
        public ?string $pageLoadStrategy = null,
        public ?string $userAgent = null,
        public array $extra = [],
    ) {
    }
}
