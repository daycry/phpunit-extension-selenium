# Getting Started

`daycry/phpunit-extension-selenium` is a PHPUnit extension that gives you a
typed, chainable Selenium WebDriver API inside your PHPUnit tests.

## Install

```bash
composer require --dev daycry/phpunit-extension-selenium
```

`php-webdriver/webdriver` is pulled in as a transitive requirement; you do
not need to install it separately.

## Register the extension

Add it to your `phpunit.xml`:

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

You can also drive every parameter through environment variables — see
[`configuration.md`](configuration.md).

## Run a Selenium hub

The simplest local setup:

```bash
docker run --rm -p 4444:4444 --shm-size=2g selenium/standalone-chrome:4
```

## Write your first test

```php
use Daycry\PHPUnit\Selenium\Asserts\SeleniumAssertions;
use Daycry\PHPUnit\Selenium\Attributes\UseSelenium;
use Daycry\PHPUnit\Selenium\Browser\Key;
use Daycry\PHPUnit\Selenium\Locator\Locator;
use Daycry\PHPUnit\Selenium\Session\SeleniumAware;
use PHPUnit\Framework\TestCase;

final class LoginTest extends TestCase
{
    use SeleniumAware;        // exposes $this->browser() + $this->selenium()
    use SeleniumAssertions;   // adds assertVisible/Text/Url/...

    #[UseSelenium]
    public function testLogin(): void
    {
        $this->browser()
            ->visit('https://example.test/login')
            ->type(Locator::name('email'), 'a@b.io')
            ->type(Locator::name('password'), 'secret')
            ->pressKey(Key::Enter)
            ->wait()->forUrl('/dashboard')->run();

        $this->assertTitleContains('Dashboard');
        $this->assertVisible(Locator::testId('user-menu'));
    }
}
```

What is happening behind the scenes:

1. `#[UseSelenium]` makes `StartTestSubscriber` materialise a per-test
   configuration and start a `SeleniumSession`. The session is pushed onto
   `SeleniumContext`.
2. `SeleniumAware` exposes that session as `$this->selenium()` and a fluent
   `Browser` facade as `$this->browser()`.
3. `SeleniumAssertions` adds Selenium-aware assertions backed by PHPUnit's
   `Assert` so failures are reported normally.
4. On `Test\Finished` the session is popped and the driver quit, regardless
   of test outcome. On `Test\Failed`, a screenshot is captured (if
   enabled) and pushed to Allure when configured.

## What to read next

| Topic                                        | File                                       |
|----------------------------------------------|--------------------------------------------|
| All XML parameters and env vars              | [configuration.md](configuration.md)       |
| Multi-browser setup (Chrome/Firefox/Edge/…)  | [browsers.md](browsers.md)                 |
| Page Object base + Components                | [page-objects.md](page-objects.md)         |
| Selenium-aware assertions reference          | [asserts.md](asserts.md)                   |
| `WaitBuilder` reference                      | [waits.md](waits.md)                       |
| Filling forms (`Select`, `Upload`, `Date`)   | [forms.md](forms.md)                       |
| Cookies + local/session storage              | [storage.md](storage.md)                   |
| Logging, Allure, screenshots, video          | [observability.md](observability.md)       |
| Architecture overview (for contributors)     | [architecture.md](architecture.md)         |
| Common errors                                | [troubleshooting.md](troubleshooting.md)   |
| Migrating from 1.x                           | [migration-v1-to-v2.md](migration-v1-to-v2.md) |
