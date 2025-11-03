# PHPUnit Selenium Extension

> A lightweight PHPUnit extension that enables opt-in Selenium WebDriver tests using a PHP 8 Attribute (`UseSelenium`). The driver is lazily created only for tests that explicitly request it.

## Key Features

- Attribute-based opt-in: `#[UseSelenium]` at class or method level
- Lazy WebDriver initialization (only when needed)
- Central capability + host configuration via `phpunit.xml`
- Automatic driver injection into test class (public `RemoteWebDriver $driver` property)
- Failure screenshots (configurable path + toggle)
- Helper actions trait (`SeleniumActions`) for common browser interactions
- Clean shutdown on test runner finish

## Requirements

| Component | Version |
|-----------|---------|
| PHP       | ^8.2    |
| PHPUnit   | ^10 || ^11 |
| php-webdriver/webdriver | ^1.15 |

## Installation

```bash
composer require --dev daycry/phpunit-extension-selenium
```

## Registering the Extension
Add the extension to your `phpunit.xml` (PHPUnit 10+/11 format):

```xml
<extensions>
    <bootstrap class="Daycry\PHPUnit\Selenium\SeleniumExtension">
        <parameter name="host" value="http://localhost:4444/wd/hub" />
        <parameter name="browser-name" value="chrome" />
        <parameter name="platform-name" value="linux" />
        <parameter name="options" value="--start-maximized,--disable-infobars,--disable-extensions" />
        <parameter name="accept-insecure-certs" value="true" />
        <parameter name="browser-version" value="" />
        <parameter name="page-load-strategy" value="normal" />
        <parameter name="user-agent" value="" />
        <parameter name="screenshot" value="true" />
        <parameter name="screenshot-path" value="./build/selenium-screenshots" />
        <!-- <parameter name="allure" value="false" /> (reserved) -->
    </bootstrap>
</extensions>
```

### Parameters Overview

| Name | Description | Example | Required |
|------|-------------|---------|----------|
| host | Selenium Grid / Standalone hub URL | http://localhost:4444/wd/hub | yes |
| browser-name | Browser name (W3C) | chrome | yes |
| platform-name | Platform (W3C) | linux | yes |
| options | Comma-separated Chrome options | --start-maximized,--disable-infobars | no |
| browser-version | Specific browser version | 120.0 | no |
| accept-insecure-certs | Allow insecure certs | true/false | no |
| page-load-strategy | auto / eager / none / normal | eager | no |
| user-agent | Custom UA string | Mozilla/5.0 ... | no |
| screenshot | Enable screenshots on failure | true/false | no |
| screenshot-path | Directory to store screenshots | ./build/selenium-screenshots | if screenshot=true |
| allure | (Reserved) Enable Allure integration | true/false | no |

## Marking Tests That Need Selenium

Apply the attribute at class level (all test methods) or per method:

```php
use Daycry\PHPUnit\Selenium\Attributes\UseSelenium;
use Daycry\PHPUnit\Selenium\Libraries\SeleniumDriver;
use PHPUnit\Framework\TestCase;
use Daycry\PHPUnit\Selenium\Traits\SeleniumActions;

#[UseSelenium]
final class LoginTest extends TestCase
{
    use SeleniumActions;

    public function testVisitLogin(): void
    {
        $this->goToUrl('https://webscorpo.local/login');
        $this->assertStringContainsString('Login', SeleniumDriver::getDriver()->getTitle());
    }
}
```

Or only for selected methods:

```php
final class MixedTest extends TestCase
{
    public ?RemoteWebDriver $driver = null;

    public function testUnitOnly(): void
    {
        $this->assertTrue(true);
    }

    #[UseSelenium]
    public function testWithBrowser(): void
    {
        $this->goToUrl('https://webscorpo.local');
        $this->assertSame('Example Domain', SeleniumDriver::getDriver()->getTitle());
    }
}
```

## Automatic Driver Injection
When a test marked with `UseSelenium` is prepared, the extension:
1. Builds capabilities from configuration (once).
2. Lazily initializes the WebDriver (first such test only).

## Helper Trait: SeleniumActions
You can mix in the `SeleniumActions` trait to get convenience methods:

```php
use Daycry\PHPUnit\Selenium\Traits\SeleniumActions;

#[UseSelenium]
final class FlowTest extends TestCase
{
    use SeleniumActions;

    public function testFlow(): void
    {
        $this->goToUrl('https://example.com');
        $this->clickElementBy('login-button', 'id');
        $this->fillFieldBy('email', 'user@example.com');
        $this->fillFieldBy('password', 'secret');
        $this->clickElementBy('submit', 'id');
        $this->waitPageLoaded('/dashboard');
    }
}
```

Available helper methods (summary):
- `goToUrl(string $url)`
- `clickElementBy(string $selector, string $attr = 'id')`
- `fillFieldBy(string $selector, string $value, string $attr = 'id', int $delayMs = 25)`
- `waitElement(string $selector, string $attr, ?string $compareText = null)`
- `waitPageLoaded(string $urlPart, int $timeout = 10)`
- `takeScreenshot(string $filename)` (used internally on failure)

## Screenshots on Failure
If `screenshot=true` and `screenshot-path` is set:
- On a failing test with `UseSelenium`, a PNG is created.
- Filename pattern: `<test-id>_<timestamp>.png` (may be adjusted in future versions).

Ensure the directory is writable. It will be created if missing.

## Troubleshooting
| Symptom | Cause | Fix |
|---------|-------|-----|
| `RuntimeException: Selenium WebDriver not initialized` | Attribute not detected or driver init failed | Ensure `#[UseSelenium]` import, check `host`, verify Selenium Grid reachable |
| Timeout waiting for element | Locator wrong / page not loaded fully | Use `waitPageLoaded()` or adjust waits |
| Screenshots not created | `screenshot` disabled or path missing | Set both `screenshot=true` and `screenshot-path` |
| Driver reused across tests causing state leakage | Single shared session | (Planned) add per-test recreation flag |
| Need headless in CI | Options missing | Add `--headless=new` to `options` parameter |

To inspect initialization errors programmatically:
```php
\Daycry\PHPUnit\Selenium\Libraries\SeleniumDriver::getLastError();
```

## Roadmap (Planned Enhancements)
- Per-test driver recreation option
- Allure attachments integration
- Headless auto-detection in CI
- Improved logging abstraction
- Test attribute options (e.g. `#[UseSelenium(recreate: true)]`)
- Capability overrides per test

## Contributing
PRs and issues welcome: https://github.com/daycry/phpunit-extension-selenium

## License
MIT
