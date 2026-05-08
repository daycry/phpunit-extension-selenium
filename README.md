# PHPUnit Selenium Extension

[![CI](https://github.com/daycry/phpunit-extension-selenium/actions/workflows/ci.yml/badge.svg)](https://github.com/daycry/phpunit-extension-selenium/actions/workflows/ci.yml)
[![Packagist Version](https://img.shields.io/packagist/v/daycry/phpunit-extension-selenium.svg)](https://packagist.org/packages/daycry/phpunit-extension-selenium)
[![PHP Version](https://img.shields.io/packagist/php-v/daycry/phpunit-extension-selenium.svg)](https://packagist.org/packages/daycry/phpunit-extension-selenium)
[![License](https://img.shields.io/packagist/l/daycry/phpunit-extension-selenium.svg)](LICENSE)
[![Downloads](https://img.shields.io/packagist/dt/daycry/phpunit-extension-selenium.svg)](https://packagist.org/packages/daycry/phpunit-extension-selenium)

> Typed, chainable Selenium WebDriver API for PHPUnit. Opt in per test through
> the `#[UseSelenium]` attribute; sessions are lazy, isolated per test, and
> support Chrome, Firefox, Edge and Safari out of the box.

## Highlights

- **Opt-in attribute**: `#[UseSelenium]` at class or method level — no driver
  cost for unmarked tests.
- **Per-test sessions** isolated through a scoped `SeleniumContext`, no
  static singletons, parallel-safe.
- **Multi-browser**: typed capabilities + factories for Chrome / Firefox /
  Edge / Safari, selectable via XML, env vars or attribute argument.
- **Fluent `Browser` facade** with typed `Locator`s (`id`, `css`, `xpath`,
  `testId`, `text`, `role`, `name`…).
- **Wait builder** with timeouts, polling, custom messages, retry-aware
  exceptions.
- **Custom assertions** (`assertVisible`, `assertText`, `assertUrlContains`,
  `assertCookie`, …) integrated with PHPUnit's counter.
- **Page Object base** + **Component** scope for reusable UI fragments.
- **Form helpers**: `Select`, `Upload`, `Date`, `fillForm()`.
- **Cookies / localStorage / sessionStorage** wrappers.
- **Frames / windows / alerts** via `withinFrame()`, `acceptAlert()` …
- **PSR-3 logging** end-to-end (Bootstrap → Start → Action → Finish/Failed).
- **Allure** integration as opt-in `suggest`; screenshots, capabilities and
  console logs attached to failed tests.
- **Configurable retries** with exponential backoff + jitter for flaky
  interactions (`StaleElementReferenceException` and friends).
- **Layered configuration**: env vars > `phpunit.xml` > built-in defaults.

## Requirements

| Component                | Version              |
|--------------------------|----------------------|
| PHP                      | `^8.2`               |
| PHPUnit                  | `^10.0 \|\| ^11.0`   |
| php-webdriver/webdriver  | `^1.15`              |

## Installation

```bash
composer require --dev daycry/phpunit-extension-selenium
```

Spin up Selenium locally:

```bash
docker run --rm -p 4444:4444 --shm-size=2g selenium/standalone-chrome:4
```

Register the extension in `phpunit.xml`:

```xml
<extensions>
    <bootstrap class="Daycry\PHPUnit\Selenium\SeleniumExtension">
        <parameter name="host" value="http://localhost:4444/wd/hub"/>
        <parameter name="browser-name" value="chrome"/>
        <parameter name="screenshot" value="true"/>
        <parameter name="screenshot-path" value="build/screenshots"/>
    </bootstrap>
</extensions>
```

## Quick example

```php
use Daycry\PHPUnit\Selenium\Asserts\SeleniumAssertions;
use Daycry\PHPUnit\Selenium\Attributes\UseSelenium;
use Daycry\PHPUnit\Selenium\Locator\Locator;
use Daycry\PHPUnit\Selenium\Session\SeleniumAware;
use PHPUnit\Framework\TestCase;

final class LoginTest extends TestCase
{
    use SeleniumAware;        // exposes $this->browser() and $this->selenium()
    use SeleniumAssertions;   // adds Selenium-aware assertions

    #[UseSelenium]
    public function testLoginRedirectsToDashboard(): void
    {
        $this->browser()
            ->visit('https://app.test/login')
            ->type(Locator::name('email'), 'a@b.io')
            ->type(Locator::name('password'), 'secret')
            ->click(Locator::testId('login-submit'))
            ->wait()->forUrl('/dashboard')->run();

        $this->assertTitleContains('Dashboard');
    }
}
```

## Per-test overrides

```php
#[UseSelenium(
    browser: 'firefox',
    profile: 'mobile',
    timeoutSeconds: 60,
    retryAttempts: 3,
    screenshot: true,
    capabilities: ['acceptInsecureCerts' => true],
    tags: ['critical', 'smoke'],
)]
public function testFlow(): void { /* ... */ }
```

The attribute is repeatable: stack on parent classes plus the method, the
closest declaration wins per field.

## Documentation

- [Getting started](docs/getting-started.md)
- [Configuration (env vars + XML + profiles)](docs/configuration.md)
- [Browsers (Chrome / Firefox / Edge / Safari)](docs/browsers.md)
- [Page Objects + Components](docs/page-objects.md)
- [Selenium-aware assertions](docs/asserts.md)
- [Troubleshooting](docs/troubleshooting.md)
- [Migration v1 → v2](docs/migration-v1-to-v2.md) and [`UPGRADE-2.0.md`](UPGRADE-2.0.md)

## Contributing

See [`CONTRIBUTING.md`](CONTRIBUTING.md). Security issues: please follow
[`SECURITY.md`](SECURITY.md) and disclose privately.

## License

[MIT](LICENSE).
