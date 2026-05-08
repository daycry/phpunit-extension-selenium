<?php

declare(strict_types=1);

namespace Daycry\PHPUnit\Selenium\Asserts;

use Daycry\PHPUnit\Selenium\Browser\Browser;
use Daycry\PHPUnit\Selenium\Locator\Locator;
use PHPUnit\Framework\Assert;

/**
 * Selenium-aware custom assertions, intended to be used by tests alongside
 * {@see \Daycry\PHPUnit\Selenium\Session\SeleniumAware} (which provides
 * `browser()`), or by Page Objects (`Page` already satisfies the contract).
 *
 * All assertions delegate to PHPUnit's Assert so they correctly increment the
 * assertion counter.
 */
trait SeleniumAssertions
{
    /**
     * Implementations must return the Browser facade against which the
     * assertions run. {@see \Daycry\PHPUnit\Selenium\Session\SeleniumAware}
     * provides one for tests; {@see \Daycry\PHPUnit\Selenium\Page\Page} for
     * Page Objects.
     */
    abstract protected function browser(): Browser;

    public function assertVisible(Locator $locator, string $message = ''): void
    {
        $element = $this->browser()->find($locator);
        Assert::assertTrue(
            $element->isDisplayed(),
            $message !== '' ? $message : \sprintf('Failed asserting %s is visible.', $locator->describe()),
        );
    }

    public function assertHidden(Locator $locator, string $message = ''): void
    {
        $isHidden = ! $this->browser()->exists($locator)
            || ! $this->browser()->find($locator)->isDisplayed();

        Assert::assertTrue(
            $isHidden,
            $message !== '' ? $message : \sprintf('Failed asserting %s is hidden.', $locator->describe()),
        );
    }

    public function assertExists(Locator $locator, string $message = ''): void
    {
        Assert::assertTrue(
            $this->browser()->exists($locator),
            $message !== '' ? $message : \sprintf('Failed asserting %s exists in DOM.', $locator->describe()),
        );
    }

    public function assertMissing(Locator $locator, string $message = ''): void
    {
        Assert::assertFalse(
            $this->browser()->exists($locator),
            $message !== '' ? $message : \sprintf('Failed asserting %s is missing from DOM.', $locator->describe()),
        );
    }

    public function assertCount(Locator $locator, int $expected, string $message = ''): void
    {
        Assert::assertCount(
            $expected,
            $this->browser()->findAll($locator),
            $message !== '' ? $message : \sprintf('Expected %d elements matching %s.', $expected, $locator->describe()),
        );
    }

    public function assertText(Locator $locator, string $expected, string $message = ''): void
    {
        Assert::assertSame(
            $expected,
            $this->browser()->find($locator)->text(),
            $message !== '' ? $message : \sprintf('Text mismatch for %s.', $locator->describe()),
        );
    }

    public function assertTextContains(Locator $locator, string $needle, string $message = ''): void
    {
        Assert::assertStringContainsString(
            $needle,
            $this->browser()->find($locator)->text(),
            $message !== '' ? $message : \sprintf('"%s" not found inside %s.', $needle, $locator->describe()),
        );
    }

    public function assertTextMatches(Locator $locator, string $pattern, string $message = ''): void
    {
        Assert::assertMatchesRegularExpression(
            $pattern,
            $this->browser()->find($locator)->text(),
            $message !== '' ? $message : \sprintf('%s text does not match %s.', $locator->describe(), $pattern),
        );
    }

    public function assertAttribute(Locator $locator, string $attribute, string $expected, string $message = ''): void
    {
        Assert::assertSame(
            $expected,
            $this->browser()->find($locator)->attribute($attribute),
            $message !== '' ? $message : \sprintf('Attribute "%s" mismatch on %s.', $attribute, $locator->describe()),
        );
    }

    public function assertHasClass(Locator $locator, string $class, string $message = ''): void
    {
        Assert::assertTrue(
            $this->browser()->find($locator)->hasClass($class),
            $message !== '' ? $message : \sprintf('%s does not have class "%s".', $locator->describe(), $class),
        );
    }

    public function assertValue(Locator $locator, string $expected, string $message = ''): void
    {
        Assert::assertSame(
            $expected,
            $this->browser()->find($locator)->value(),
            $message !== '' ? $message : \sprintf('Value mismatch on %s.', $locator->describe()),
        );
    }

    public function assertChecked(Locator $locator, string $message = ''): void
    {
        Assert::assertTrue(
            $this->browser()->find($locator)->isSelected(),
            $message !== '' ? $message : \sprintf('%s is not checked.', $locator->describe()),
        );
    }

    public function assertNotChecked(Locator $locator, string $message = ''): void
    {
        Assert::assertFalse(
            $this->browser()->find($locator)->isSelected(),
            $message !== '' ? $message : \sprintf('%s is unexpectedly checked.', $locator->describe()),
        );
    }

    public function assertEnabled(Locator $locator, string $message = ''): void
    {
        Assert::assertTrue(
            $this->browser()->find($locator)->isEnabled(),
            $message !== '' ? $message : \sprintf('%s is not enabled.', $locator->describe()),
        );
    }

    public function assertDisabled(Locator $locator, string $message = ''): void
    {
        Assert::assertFalse(
            $this->browser()->find($locator)->isEnabled(),
            $message !== '' ? $message : \sprintf('%s is unexpectedly enabled.', $locator->describe()),
        );
    }

    public function assertUrlIs(string $expected, string $message = ''): void
    {
        Assert::assertSame($expected, $this->browser()->currentUrl(), $message);
    }

    public function assertUrlContains(string $needle, string $message = ''): void
    {
        Assert::assertStringContainsString($needle, $this->browser()->currentUrl(), $message);
    }

    public function assertUrlMatches(string $pattern, string $message = ''): void
    {
        Assert::assertMatchesRegularExpression($pattern, $this->browser()->currentUrl(), $message);
    }

    public function assertTitle(string $expected, string $message = ''): void
    {
        Assert::assertSame($expected, $this->browser()->title(), $message);
    }

    public function assertTitleContains(string $needle, string $message = ''): void
    {
        Assert::assertStringContainsString($needle, $this->browser()->title(), $message);
    }

    public function assertCookie(string $name, ?string $expected = null, string $message = ''): void
    {
        $cookie = $this->browser()->driver()->manage()->getCookieNamed($name);

        Assert::assertNotNull($cookie, $message !== '' ? $message : \sprintf('Cookie "%s" missing.', $name));

        if ($expected !== null) {
            Assert::assertSame($expected, $cookie->getValue(), $message);
        }
    }
}
