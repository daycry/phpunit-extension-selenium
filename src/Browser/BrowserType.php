<?php

declare(strict_types=1);

namespace Daycry\PHPUnit\Selenium\Browser;

use Daycry\PHPUnit\Selenium\Exception\ConfigurationException;
use ValueError;

enum BrowserType: string
{
    case Chrome = 'chrome';
    case Firefox = 'firefox';
    case Edge = 'edge';
    case Safari = 'safari';

    public static function fromName(string $name): self
    {
        try {
            return self::from(strtolower(trim($name)));
        } catch (ValueError $e) {
            throw new ConfigurationException(
                \sprintf('Unsupported browser "%s". Supported: chrome, firefox, edge, safari.', $name),
                0,
                $e,
            );
        }
    }
}
