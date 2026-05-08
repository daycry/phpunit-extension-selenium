<?php

declare(strict_types=1);

namespace Daycry\PHPUnit\Selenium\Browser;

use Daycry\PHPUnit\Selenium\Locator\Locator;
use Facebook\WebDriver\Remote\RemoteWebElement;

/**
 * Immutable element handle returned by query methods (Browser::find, Browser::findAll).
 *
 * Mutating operations live on Browser to keep query/command separation explicit.
 */
final readonly class Element
{
    public function __construct(
        public Locator $locator,
        private RemoteWebElement $remote,
    ) {
    }

    public function text(): string
    {
        return $this->remote->getText();
    }

    public function attribute(string $name): ?string
    {
        return $this->remote->getAttribute($name);
    }

    public function value(): ?string
    {
        return $this->attribute('value');
    }

    public function isDisplayed(): bool
    {
        return $this->remote->isDisplayed();
    }

    public function isEnabled(): bool
    {
        return $this->remote->isEnabled();
    }

    public function isSelected(): bool
    {
        return $this->remote->isSelected();
    }

    public function tagName(): string
    {
        return $this->remote->getTagName();
    }

    /**
     * @return list<string>
     */
    public function classes(): array
    {
        $class = $this->attribute('class') ?? '';

        return array_values(array_filter(explode(' ', $class), static fn (string $c): bool => $c !== ''));
    }

    public function hasClass(string $class): bool
    {
        return \in_array($class, $this->classes(), true);
    }

    public function remote(): RemoteWebElement
    {
        return $this->remote;
    }
}
