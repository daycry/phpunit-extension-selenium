# Selenium Assertions

The `SeleniumAssertions` trait adds Selenium-aware assertions on top of
PHPUnit's built-ins. They all increment the PHPUnit assertion counter
through `Assert::*` so failures are reported normally.

The trait declares an abstract `browser(): Browser` method which is
satisfied by:

- `SeleniumAware` (for tests, `$this->browser()` resolves the current
  session lazily), or
- `Page` / `Component` (for Page Objects, the Browser is constructor
  injected).

So in practice you `use SeleniumAware, SeleniumAssertions;` together in
tests, or extend `Page` (which already uses `SeleniumAssertions`) for
page objects.

## Available assertions

| Assertion                                       | Purpose                                  |
|-------------------------------------------------|------------------------------------------|
| `assertVisible(Locator)`                        | Element exists and is displayed          |
| `assertHidden(Locator)`                         | Element absent or not displayed          |
| `assertExists(Locator)`                         | Element present in DOM                   |
| `assertMissing(Locator)`                        | Element not present in DOM               |
| `assertCount(Locator, int)`                     | Count of matching elements               |
| `assertText(Locator, string)`                   | Exact text match                         |
| `assertTextContains(Locator, string)`           | Substring match                          |
| `assertTextMatches(Locator, string $regex)`     | Regex match                              |
| `assertAttribute(Locator, string, string)`      | Attribute value match                    |
| `assertHasClass(Locator, string)`               | Element has CSS class                    |
| `assertValue(Locator, string)`                  | `value` attribute match (forms)          |
| `assertChecked(Locator)` / `assertNotChecked`   | Checkbox/radio state                     |
| `assertEnabled(Locator)` / `assertDisabled`     | Element enablement                       |
| `assertUrlIs` / `assertUrlContains` / `assertUrlMatches` | Current URL                      |
| `assertTitle(string)` / `assertTitleContains`   | Page title                               |
| `assertCookie(string, ?string)`                 | Cookie present, optionally with value    |

Every assertion accepts an optional `string $message` last argument to
attach context to the failure.

## Using them in a test

```php
use Daycry\PHPUnit\Selenium\Asserts\SeleniumAssertions;
use Daycry\PHPUnit\Selenium\Attributes\UseSelenium;
use Daycry\PHPUnit\Selenium\Locator\Locator;
use Daycry\PHPUnit\Selenium\Session\SeleniumAware;
use PHPUnit\Framework\TestCase;

final class CheckoutTest extends TestCase
{
    use SeleniumAware;
    use SeleniumAssertions;

    #[UseSelenium]
    public function testCheckoutTotal(): void
    {
        $this->browser()->visit('/cart');

        $this->assertVisible(Locator::testId('cart-summary'));
        $this->assertText(Locator::testId('total'), '€42.00');
        $this->assertEnabled(Locator::testId('checkout-cta'));
        $this->assertCount(Locator::css('.cart-item'), 3);
        $this->assertUrlContains('/cart');
        $this->assertCookie('session');
    }
}
```

## Inside Page Objects

`Page` already uses the `SeleniumAssertions` trait and provides the
`browser()` method, so subclasses can call `$this->assertVisible(...)`
directly. See [page-objects.md](page-objects.md).

## Choosing between assertions and `WaitBuilder`

Assertions are immediate: they read the DOM once and pass/fail. If the UI
needs time to settle, drive a `wait()->...->run()` first, then assert. Or
combine them: an assertion right after a navigation that is known to be
synchronous, a wait when crossing an async boundary (XHR, animation,
client-side route).

See [waits.md](waits.md) for the full `WaitBuilder` reference.
