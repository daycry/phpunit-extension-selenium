<?php

declare(strict_types=1);

namespace Daycry\PHPUnit\Selenium\Locator;

use Facebook\WebDriver\WebDriverBy;

final readonly class LocatorResolver
{
    public function __construct(
        private string $testIdAttribute = 'data-testid',
    ) {
    }

    public function toBy(Locator $locator): WebDriverBy
    {
        return match ($locator->strategy) {
            LocatorStrategy::Id => WebDriverBy::id($locator->value),
            LocatorStrategy::Css => WebDriverBy::cssSelector($locator->value),
            LocatorStrategy::XPath => WebDriverBy::xpath($locator->value),
            LocatorStrategy::Name => WebDriverBy::name($locator->value),
            LocatorStrategy::ClassName => WebDriverBy::className($locator->value),
            LocatorStrategy::TagName => WebDriverBy::tagName($locator->value),
            LocatorStrategy::LinkText => WebDriverBy::linkText($locator->value),
            LocatorStrategy::PartialLinkText => WebDriverBy::partialLinkText($locator->value),
            LocatorStrategy::TestId => WebDriverBy::cssSelector(\sprintf('[%s="%s"]', $this->testIdAttribute, $this->escapeCssAttribute($locator->value))),
            LocatorStrategy::Text => WebDriverBy::xpath(\sprintf('//*[normalize-space(text())=%s]', $this->escapeXpathLiteral($locator->value))),
            LocatorStrategy::Role => WebDriverBy::cssSelector(\sprintf('[role="%s"]', $this->escapeCssAttribute($locator->value))),
        };
    }

    private function escapeCssAttribute(string $value): string
    {
        return str_replace(['\\', '"'], ['\\\\', '\\"'], $value);
    }

    private function escapeXpathLiteral(string $value): string
    {
        if (! str_contains($value, "'")) {
            return "'" . $value . "'";
        }

        if (! str_contains($value, '"')) {
            return '"' . $value . '"';
        }

        $parts = explode("'", $value);

        return 'concat(' . implode(", \"'\", ", array_map(static fn (string $p): string => "'" . $p . "'", $parts)) . ')';
    }
}
