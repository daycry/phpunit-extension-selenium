<?php

declare(strict_types=1);

namespace Daycry\PHPUnit\Selenium\Form;

/**
 * Tagged value object describing how to pick a `<select>` option.
 *
 * Used by Browser::fillForm() to dispatch to the right WebDriverSelect call
 * without sniffing strings.
 */
final readonly class Select
{
    public const BY_VALUE = 'value';
    public const BY_LABEL = 'label';
    public const BY_INDEX = 'index';

    private function __construct(
        public string $strategy,
        public string|int $value,
    ) {
    }

    public static function byValue(string $value): self
    {
        return new self(self::BY_VALUE, $value);
    }

    public static function byLabel(string $label): self
    {
        return new self(self::BY_LABEL, $label);
    }

    public static function byIndex(int $index): self
    {
        return new self(self::BY_INDEX, $index);
    }
}
