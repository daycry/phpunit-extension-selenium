# Upgrading from 1.x to 2.0

This guide lists every breaking change introduced by 2.0 and the equivalent
v2 API. Most v1 setups will keep working at runtime because v2 adds new
namespaces alongside the v1 ones, but the recommended migration is to move
your tests to the v2 API and drop the v1 traits/singletons entirely.

## Required environment

| Requirement      | v1.x                  | v2.0                                |
|------------------|-----------------------|-------------------------------------|
| PHP              | `^8.2`                | `^8.2` (8.3+ recommended)           |
| PHPUnit          | `^10.0 \|\| ^11.0`    | `^10.0 \|\| ^11.0`                  |
| php-webdriver    | `^1.15`               | `^1.15`                             |

## What you must change

### 1. The `SeleniumDriver` static singleton is replaced by per-test sessions

**v1:**
```php
SeleniumDriver::getDriver()->findElement(WebDriverBy::id('foo'));
```

**v2:**
```php
use Daycry\PHPUnit\Selenium\Locator\Locator;
use Daycry\PHPUnit\Selenium\Session\SeleniumAware;

final class MyTest extends TestCase
{
    use SeleniumAware;

    #[UseSelenium]
    public function testFoo(): void
    {
        // High-level (preferred): the chainable Browser facade.
        $this->browser()->click(Locator::id('foo'));

        // Low-level escape hatch: raw RemoteWebDriver, when needed.
        $this->selenium()->driver()->findElement(\Facebook\WebDriver\WebDriverBy::id('foo'));
    }
}
```

`$this->browser()` returns the `Browser` facade bound to the current test;
`$this->selenium()` returns the underlying `SeleniumSession` if you need
WebDriver-specific calls. Sessions are created on `Test\Prepared`, pushed
onto a scoped context, and popped/closed on `Test\Finished`. There is no
global singleton anymore, which makes parallel data providers and
isolation safe.

### 2. The `SeleniumActions` trait is replaced by the `Browser` facade

The new `Browser` facade is chainable and locator-typed. It is exposed via
the `SeleniumAware` trait or constructed manually in Page Objects.

Inside a test class that uses `SeleniumAware`, the facade is reachable as
`$this->browser()`:

| v1 (SeleniumActions)                                  | v2 (Browser)                                                                 |
|-------------------------------------------------------|------------------------------------------------------------------------------|
| `$this->goToUrl($url)`                                | `$this->browser()->visit($url)`                                              |
| `$this->clickElementBy($k, 'css')`                    | `$this->browser()->click(Locator::css($k))`                                  |
| `$this->fillFieldBy($k, $v, 'name', 25)`              | `$this->browser()->type(Locator::name($k), $v, delayMs: 25)`                 |
| `$this->waitElement($k, 'id', ['compareText' => 'X'])`| `$this->browser()->waitFor(Locator::id($k))` + `assertTextContains(..., 'X')`|
| `$this->waitPageLoaded('/dash')`                      | `$this->browser()->wait()->forUrl('/dash')->run()`                           |
| `$this->getValueFromElement($k, 'id', 'value')`       | `$this->browser()->find(Locator::id($k))->attribute('value')`                |
| `$this->waitDialogUntilOpen($k, 'id')`                | `$this->browser()->wait()->forFunction(...)->run()`                          |
| `$this->takeScreenshot($f)`                           | `$this->browser()->screenshot($f)`                                           |

In Page Objects, the facade is constructor-injected and reachable through
the `$this->browser` property (no method call).

Locator types: `Locator::id()`, `Locator::css()`, `Locator::xpath()`,
`Locator::name()`, `Locator::testId()`, `Locator::text()`, `Locator::role()`,
`Locator::linkText()`, `Locator::partialLinkText()`, `Locator::tagName()`,
`Locator::className()`. Pass them to every Browser command instead of the
`(string $key, string $attr)` pair.

### 3. The `#[UseSelenium]` attribute now accepts named arguments

The bare form `#[UseSelenium]` keeps working unchanged. Optional arguments
override per-test config:

```php
#[UseSelenium(
    browser: 'firefox',
    profile: 'mobile',
    timeoutSeconds: 60,
    retryAttempts: 3,
    screenshot: true,
    capabilities: ['goog:chromeOptions' => ['mobileEmulation' => ['deviceName' => 'Pixel 5']]],
    tags: ['critical', 'smoke'],
)]
```

The attribute is `IS_REPEATABLE`: stack it on parent classes plus the method
to layer overrides — method-level wins, then closest subclass, up to the
root class.

### 4. Configuration: env vars and profiles

XML parameters keep working. Env vars now overlay XML; this is the
recommended way to vary configuration per CI job. Names:

```
SELENIUM_HOST                    Selenium hub / standalone URL
SELENIUM_BROWSER                 chrome | firefox | edge | safari
SELENIUM_PROFILE                 named profile from your config
SELENIUM_TIMEOUT_PAGE_LOAD_MS
SELENIUM_TIMEOUT_EXPLICIT_MS
SELENIUM_RETRY_MAX_ATTEMPTS
SELENIUM_RETRY_INITIAL_DELAY_MS
SELENIUM_RETRY_MULTIPLIER
SELENIUM_SCREENSHOT              true|false
SELENIUM_SCREENSHOT_PATH
SELENIUM_SCREENSHOT_MODE         off | on-failure | every-step
SELENIUM_SCREENSHOT_FORMAT       png | webp
SELENIUM_ALLURE                  true|false
SELENIUM_REPORT_PATH
```

### 5. Configuration is now strongly typed

Internally, `SeleniumExtension` builds a typed `SeleniumConfig` (with
`RemoteEndpoint`, `BrowserConfig`, `TimeoutConfig`, `RetryConfig`,
`ScreenshotConfig`, `ReportingConfig`) instead of a flat array of arguments.
Custom subclasses of v1's `ConfigurationSubscriber` will not survive: build
a `ConfigSource` instead and add it through your own bootstrapping.

### 6. Errors now surface through real exceptions

`SeleniumExtension::bootstrap()` no longer prints to stdout and calls `exit`.
A failure raises `Daycry\PHPUnit\Selenium\Exception\ExtensionBootstrapException`
which PHPUnit reports through its normal channels.

`SeleniumActions::takeScreenshot()` no longer swallows `WebDriverException`;
failures bubble up as `Daycry\PHPUnit\Selenium\Exception\ScreenshotException`.

### 7. Screenshots are filename-sanitised

Old format: `Ymd_His` (no metadata).

New format: `{ISO8601}_{class}_{method}_{browser}_{status}_{shortSession}.png`.
Set `SELENIUM_SCREENSHOT_PATH` (or the XML parameter `screenshot-path`) to
move them; the default is `sys_get_temp_dir().'/selenium-screenshots'`.

### 8. Allure integration is opt-in

The previous `allure` parameter was read but never used. v2 wires up
`Daycry\PHPUnit\Selenium\Reporting\AllureReporter` when:

1. The `allure` parameter is `true` (or `SELENIUM_ALLURE=true`).
2. `allure-framework/allure-phpunit` is installed (declared as `suggest`).

Without the package the reporter is a no-op — installs do not fail.

## What still works

- `#[UseSelenium]` without arguments.
- The list of helper method names on `SeleniumActions` keeps the same
  signatures during the v2.x line. They emit `E_USER_DEPRECATED` and will
  be removed in 3.0. Migration is mechanical — see the table above.
- The PHPUnit extension registration mechanism in `phpunit.xml`:

  ```xml
  <extensions>
    <bootstrap class="Daycry\PHPUnit\Selenium\SeleniumExtension">
      <parameter name="host" value="http://localhost:4444/wd/hub"/>
      <parameter name="browser-name" value="chrome"/>
    </bootstrap>
  </extensions>
  ```

## Suggested migration order

1. Update `composer.json` to `^2.0`, run `composer update`.
2. Run your existing suite — v1 traits still work, you should see the same
   outcome plus deprecation notices.
3. Convert one test class to the new API as a pilot (replace `SeleniumActions`
   with `SeleniumAware`, use `Browser` + `Locator`).
4. Once convinced, migrate the rest mechanically (search-and-replace using
   the table above).
5. Add a `tests/Feature/` group for end-to-end tests against a real grid in
   CI; the new `integration.yml` workflow shows the expected setup.
