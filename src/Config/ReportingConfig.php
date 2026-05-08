<?php

declare(strict_types=1);

namespace Daycry\PHPUnit\Selenium\Config;

final readonly class ReportingConfig
{
    public function __construct(
        public bool $allure = false,
        public ?string $reportPath = null,
        public bool $captureBrowserConsole = true,
        public bool $videoEnabled = false,
    ) {
    }
}
