<?php

declare(strict_types=1);

namespace Daycry\PHPUnit\Selenium\Locator;

final readonly class Locator
{
    public function __construct(
        public LocatorStrategy $strategy,
        public string $value,
    ) {
    }

    public static function id(string $value): self
    {
        return new self(LocatorStrategy::Id, $value);
    }

    public static function css(string $value): self
    {
        return new self(LocatorStrategy::Css, $value);
    }

    public static function xpath(string $value): self
    {
        return new self(LocatorStrategy::XPath, $value);
    }

    public static function name(string $value): self
    {
        return new self(LocatorStrategy::Name, $value);
    }

    public static function className(string $value): self
    {
        return new self(LocatorStrategy::ClassName, $value);
    }

    public static function tagName(string $value): self
    {
        return new self(LocatorStrategy::TagName, $value);
    }

    public static function linkText(string $value): self
    {
        return new self(LocatorStrategy::LinkText, $value);
    }

    public static function partialLinkText(string $value): self
    {
        return new self(LocatorStrategy::PartialLinkText, $value);
    }

    public static function testId(string $value): self
    {
        return new self(LocatorStrategy::TestId, $value);
    }

    public static function text(string $value): self
    {
        return new self(LocatorStrategy::Text, $value);
    }

    public static function role(string $value): self
    {
        return new self(LocatorStrategy::Role, $value);
    }

    public function describe(): string
    {
        return \sprintf('%s=%s', $this->strategy->value, $this->value);
    }
}
