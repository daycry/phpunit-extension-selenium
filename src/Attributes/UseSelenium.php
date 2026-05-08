<?php

declare(strict_types=1);

namespace Daycry\PHPUnit\Selenium\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final readonly class UseSelenium
{
    /**
     * @param array<string, scalar|array<mixed>> $capabilities Raw capability overlay applied verbatim on top of the resolved capabilities.
     * @param list<string> $tags Free-form labels surfaced through reporting.
     */
    public function __construct(
        public ?string $browser = null,
        public ?string $profile = null,
        public ?int $timeoutSeconds = null,
        public ?int $pageLoadTimeoutMs = null,
        public ?int $retryAttempts = null,
        public ?bool $screenshot = null,
        public array $capabilities = [],
        public ?string $browserVersion = null,
        public ?string $platform = null,
        public array $tags = [],
    ) {
    }
}
