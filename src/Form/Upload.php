<?php

declare(strict_types=1);

namespace Daycry\PHPUnit\Selenium\Form;

use Daycry\PHPUnit\Selenium\Exception\ConfigurationException;

final readonly class Upload
{
    private function __construct(public string $path)
    {
    }

    public static function file(string $path): self
    {
        if (! is_file($path)) {
            throw new ConfigurationException(\sprintf('Upload target file does not exist: %s', $path));
        }

        return new self($path);
    }
}
