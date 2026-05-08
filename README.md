# PHPUnit Selenium Extension

> Typed, chainable Selenium WebDriver API for PHPUnit. Opt in per test through
> the `#[UseSelenium]` attribute; sessions are lazy, isolated per test, and
> support Chrome, Firefox, Edge and Safari out of the box.

### Package

[![Latest Stable Version](https://img.shields.io/packagist/v/daycry/phpunit-extension-selenium.svg?label=stable)](https://packagist.org/packages/daycry/phpunit-extension-selenium)
[![Total Downloads](https://img.shields.io/packagist/dt/daycry/phpunit-extension-selenium.svg)](https://packagist.org/packages/daycry/phpunit-extension-selenium)
[![Monthly Downloads](https://img.shields.io/packagist/dm/daycry/phpunit-extension-selenium.svg)](https://packagist.org/packages/daycry/phpunit-extension-selenium)
[![PHP Version Require](https://img.shields.io/packagist/dependency-v/daycry/phpunit-extension-selenium/php?color=8892bf)](https://packagist.org/packages/daycry/phpunit-extension-selenium)
[![License](https://img.shields.io/github/license/daycry/phpunit-extension-selenium)](https://github.com/daycry/phpunit-extension-selenium/blob/master/LICENSE)

### Quality

[![PHPUnit](https://github.com/daycry/phpunit-extension-selenium/actions/workflows/phpunit.yml/badge.svg)](https://github.com/daycry/phpunit-extension-selenium/actions/workflows/phpunit.yml)
[![PHPStan](https://github.com/daycry/phpunit-extension-selenium/actions/workflows/phpstan.yml/badge.svg)](https://github.com/daycry/phpunit-extension-selenium/actions/workflows/phpstan.yml)
[![Rector](https://github.com/daycry/phpunit-extension-selenium/actions/workflows/rector.yml/badge.svg)](https://github.com/daycry/phpunit-extension-selenium/actions/workflows/rector.yml)
[![Code Style](https://github.com/daycry/phpunit-extension-selenium/actions/workflows/code-style.yml/badge.svg)](https://github.com/daycry/phpunit-extension-selenium/actions/workflows/code-style.yml)
[![Infection](https://github.com/daycry/phpunit-extension-selenium/actions/workflows/infection.yml/badge.svg)](https://github.com/daycry/phpunit-extension-selenium/actions/workflows/infection.yml)
[![Integration](https://github.com/daycry/phpunit-extension-selenium/actions/workflows/integration.yml/badge.svg)](https://github.com/daycry/phpunit-extension-selenium/actions/workflows/integration.yml)
[![Coverage Status](https://codecov.io/gh/daycry/phpunit-extension-selenium/branch/master/graph/badge.svg)](https://codecov.io/gh/daycry/phpunit-extension-selenium)

### Community

[![GitHub stars](https://img.shields.io/github/stars/daycry/phpunit-extension-selenium?style=social)](https://github.com/daycry/phpunit-extension-selenium/stargazers)
[![Donate](https://img.shields.io/badge/Donate-PayPal-blue.svg)](https://www.paypal.com/donate?business=SYC5XDT23UZ5G&no_recurring=0&item_name=Thank+you%21&currency_code=EUR)

---

## Table of contents

- [Highlights](#highlights)
- [Requirements](#requirements)
- [Installation](#installation)
- [Configure the extension](#configure-the-extension)
- [Run a Selenium hub](#run-a-selenium-hub)
- [Write your first test](#write-your-first-test)
- [Per-test overrides](#per-test-overrides)
- [Page Objects](#page-objects)
- [What you get out of the box](#what-you-get-out-of-the-box)
- [Documentation](#documentation)
- [Versioning and migrations](#versioning-and-migrations)
- [Contributing](#contributing)
- [License](#license)

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
- **Allure** integration as opt-in dependency; screenshots, capabilities and
  console logs attached to failed tests.
- **Configurable retries** with exponential backoff + jitter for flaky
  interactions (`StaleElementReferenceException` and friends).
- **Layered configuration**: env vars > `phpunit.xml` > built-in defaults.

## Requirements

| Component                | Version              |
|--------------------------|----------------------|
| PHP                      | `^8.2`               |
| PHPUnit                  | `^10.0 \|\| ^11.0`   |
| php-webdriver/webdriver  | `^1.15` (transitive) |
| A reachable Selenium hub | `4.x`                |

`php-webdriver/webdriver` is pulled in as a transitive dependency — you do
not need to install it separately.

## Installation

```bash
composer require --dev daycry/phpunit-extension-selenium
```

That's the only mandatory step. Optional extras:

```bash
# Allure reporting (screenshots + console logs attached to failed tests)
composer require --dev allure-framework/allure-phpunit

# Mutation testing for your own suite (already a dev-dep of this lib in
# its own repo, but you may want it for downstream CI as well)
composer require --dev infection/infection
```

If you don't install `allure-phpunit`, the Allure integration silently
degrades to a no-op — your tests keep running unchanged. See
[`docs/observability.md`](docs/observability.md) for the full reporting
setup.

## Configure the extension

Register `SeleniumExtension` in your `phpunit.xml` (or `phpunit.xml.dist`):

```xml
<phpunit
    bootstrap="vendor/autoload.php"
    cacheDirectory=".phpunit.cache"
    colors="true"
>
    <extensions>
        <bootstrap class="Daycry\PHPUnit\Selenium\SeleniumExtension">
            <parameter name="host"             value="http://localhost:4444/wd/hub"/>
            <parameter name="browser-name"     value="chrome"/>
            <parameter name="screenshot"       value="true"/>
            <parameter name="screenshot-path"  value="build/screenshots"/>
        </bootstrap>
    </extensions>

    <testsuites>
        <testsuite name="default">
            <directory>tests</directory>
        </testsuite>
    </testsuites>
</phpunit>
```

Every parameter is optional and can also be driven through environment
variables (env wins over XML wins over defaults). The full list lives in
[`docs/configuration.md`](docs/configuration.md).

## Run a Selenium hub

The simplest local setup uses Selenium's official standalone images:

```bash
# Chrome
docker run --rm -p 4444:4444 --shm-size=2g selenium/standalone-chrome:4

# Firefox
docker run --rm -p 4444:4444 --shm-size=2g selenium/standalone-firefox:4

# Edge
docker run --rm -p 4444:4444 --shm-size=2g selenium/standalone-edge:4
```

For multi-browser CI runs, see the reference setup in
[`.github/workflows/integration.yml`](.github/workflows/integration.yml)
and the longer write-up in [`docs/browsers.md`](docs/browsers.md).

## Write your first test

```php
<?php

declare(strict_types=1);

use Daycry\PHPUnit\Selenium\Asserts\SeleniumAssertions;
use Daycry\PHPUnit\Selenium\Attributes\UseSelenium;
use Daycry\PHPUnit\Selenium\Browser\Key;
use Daycry\PHPUnit\Selenium\Locator\Locator;
use Daycry\PHPUnit\Selenium\Session\SeleniumAware;
use PHPUnit\Framework\TestCase;

final class LoginTest extends TestCase
{
    use SeleniumAware;        // $this->browser() + $this->selenium()
    use SeleniumAssertions;   // assertVisible / assertText / assertUrl…

    #[UseSelenium]
    public function testLoginRedirectsToDashboard(): void
    {
        $this->browser()
            ->visit('https://app.test/login')
            ->type(Locator::name('email'), 'a@b.io')
            ->type(Locator::name('password'), 'secret')
            ->pressKey(Key::Enter)
            ->wait()->forUrl('/dashboard')->run();

        $this->assertTitleContains('Dashboard');
        $this->assertVisible(Locator::testId('user-menu'));
    }
}
```

What happens:

1. `#[UseSelenium]` triggers `StartTestSubscriber` to materialise a per-test
   configuration and start a `SeleniumSession`.
2. `SeleniumAware` exposes that session as `$this->browser()` (chainable
   facade) and `$this->selenium()` (raw `RemoteWebDriver` access).
3. `SeleniumAssertions` adds Selenium-aware assertions backed by PHPUnit's
   `Assert` so failures show up in the normal test report.
4. On `Test\Finished` the session is popped from `SeleniumContext` and the
   driver is quit. On `Test\Failed`, a screenshot is captured and
   forwarded to Allure when configured.

## Per-test overrides

`#[UseSelenium]` accepts named arguments to override the resolved
configuration for one test:

```php
#[UseSelenium(
    browser: 'firefox',
    profile: 'mobile',
    timeoutSeconds: 60,
    pageLoadTimeoutMs: 15_000,
    retryAttempts: 3,
    screenshot: true,
    capabilities: ['acceptInsecureCerts' => true],
    browserVersion: 'latest',
    platform: 'linux',
    tags: ['critical', 'smoke'],
)]
public function testFlow(): void { /* ... */ }
```

The attribute is `IS_REPEATABLE`: stack it on parent classes plus the
method to layer overrides; the closest declaration wins per field. See
[`docs/configuration.md`](docs/configuration.md#per-test-overrides) for
every supported argument.

## Page Objects

```php
use Daycry\PHPUnit\Selenium\Browser\Browser;
use Daycry\PHPUnit\Selenium\Locator\Locator;
use Daycry\PHPUnit\Selenium\Page\Page;

final class LoginPage extends Page
{
    public function url(): string
    {
        return '/login';
    }

    public function loginAs(string $user, string $password): DashboardPage
    {
        $this->browser
            ->type(Locator::name('email'), $user)
            ->type(Locator::name('password'), $password)
            ->click(Locator::testId('login-submit'));

        return new DashboardPage($this->browser);
    }
}
```

```php
#[UseSelenium]
public function testLogin(): void
{
    (new LoginPage($this->browser()))
        ->visit()
        ->loginAs('a@b.io', 'secret');

    $this->browser()->wait()->forUrl('/dashboard')->run();
}
```

Read more in [`docs/page-objects.md`](docs/page-objects.md), including the
`Component` base class for reusable UI fragments.

## What you get out of the box

| Capability                                  | Read more                                                     |
|---------------------------------------------|---------------------------------------------------------------|
| Chainable element interactions              | [`docs/getting-started.md`](docs/getting-started.md)          |
| Typed locators (`id`, `css`, `testId`, `text`, `role`…) | [`docs/getting-started.md`](docs/getting-started.md) |
| Fluent waits (10 conditions, custom messages) | [`docs/waits.md`](docs/waits.md)                            |
| Selenium-aware assertions                   | [`docs/asserts.md`](docs/asserts.md)                          |
| Page Object base + Components               | [`docs/page-objects.md`](docs/page-objects.md)                |
| Form helpers (`Select`, `Upload`, `Date`, `fillForm`) | [`docs/forms.md`](docs/forms.md)                    |
| Cookies + localStorage + sessionStorage     | [`docs/storage.md`](docs/storage.md)                          |
| Multi-browser (Chrome / Firefox / Edge / Safari) | [`docs/browsers.md`](docs/browsers.md)                   |
| PSR-3 logging, Allure, screenshots, video   | [`docs/observability.md`](docs/observability.md)              |
| Architecture overview                       | [`docs/architecture.md`](docs/architecture.md)                |
| Diagnosing common errors                    | [`docs/troubleshooting.md`](docs/troubleshooting.md)          |

## Documentation

The full documentation set lives under [`docs/`](docs/index.md):

- [Getting started](docs/getting-started.md) — installation, register the
  extension, write your first test.
- [Configuration](docs/configuration.md) — XML parameters, environment
  variables, profiles, per-test overrides.
- [Browsers](docs/browsers.md) — switching between Chrome, Firefox, Edge,
  Safari.
- [Page Objects + Components](docs/page-objects.md)
- [Selenium-aware assertions](docs/asserts.md)
- [Waits (`WaitBuilder`)](docs/waits.md)
- [Forms (`Select`, `Upload`, `Date`, `fillForm`)](docs/forms.md)
- [Cookies and storage](docs/storage.md)
- [Observability (PSR-3 logging, Allure, screenshots, video)](docs/observability.md)
- [Architecture](docs/architecture.md) — service graph, lifecycle, layers,
  extension points.
- [Troubleshooting](docs/troubleshooting.md)

## Versioning and migrations

This project follows [Semantic Versioning](https://semver.org/). Release
notes live in [`CHANGELOG.md`](CHANGELOG.md).

Coming from 1.x? Read [`UPGRADE-2.0.md`](UPGRADE-2.0.md) for the full
upgrade guide and [`docs/migration-v1-to-v2.md`](docs/migration-v1-to-v2.md)
for a quick lookup table.

## Contributing

See [`CONTRIBUTING.md`](CONTRIBUTING.md) for the local toolchain
(`composer ci` runs the same checks as CI), the
[Conventional Commits](https://www.conventionalcommits.org/) convention
used by the release workflow, and the architectural rules enforced by
[Deptrac](deptrac.yaml).

Security issues: please follow [`SECURITY.md`](SECURITY.md) and disclose
privately rather than opening a public GitHub issue.

## License

This project is open source under the [MIT license](LICENSE).
