<?php

declare(strict_types=1);

namespace Daycry\PHPUnit\Selenium\Config;

use Daycry\PHPUnit\Selenium\Exception\ConfigurationException;

enum ScreenshotMode: string
{
    case Off = 'off';
    case OnFailure = 'on-failure';
    case EveryStep = 'every-step';
}

enum ScreenshotFormat: string
{
    case Png = 'png';
    case Webp = 'webp';
}

final readonly class ScreenshotConfig
{
    public function __construct(
        public bool $enabled = false,
        public ?string $path = null,
        public ScreenshotMode $mode = ScreenshotMode::OnFailure,
        public ScreenshotFormat $format = ScreenshotFormat::Png,
    ) {
        if ($this->enabled && $this->path !== null && trim($this->path) === '') {
            throw new ConfigurationException('Screenshot path cannot be empty when screenshots are enabled.');
        }

        if ($this->enabled && $this->path !== null) {
            $parent = \dirname($this->path);
            if (! is_dir($parent) && ! @mkdir($parent, 0o777, true) && ! is_dir($parent)) {
                throw new ConfigurationException(\sprintf('Screenshot directory parent "%s" is not writable.', $parent));
            }
        }

        if ($this->format === ScreenshotFormat::Webp && ! \function_exists('imagewebp')) {
            throw new ConfigurationException('WebP screenshot format requires the GD extension with WebP support.');
        }
    }

    public function isOn(): bool
    {
        return $this->enabled && $this->mode !== ScreenshotMode::Off;
    }
}
