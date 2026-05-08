# Troubleshooting

This page lists the most common errors, their root causes and how to fix
them. If yours is not listed, please open an issue with the test code,
the relevant XML/env configuration, and the chained exception output.

## "Failed to bootstrap extension"

`Daycry\PHPUnit\Selenium\Exception\ExtensionBootstrapException` wraps the
underlying configuration error. Common causes:

- **Invalid host URL**: `host` (or `SELENIUM_HOST`) must be a valid URL.
- **Unwritable screenshot path**: when `screenshot=true`, the parent of
  `screenshot-path` must exist and be writable.
- **Unsupported browser**: `browser-name` must be `chrome`, `firefox`,
  `edge` or `safari`.
- **WebP without GD**: `screenshot-format=webp` requires the GD extension
  with WebP support. Drop the parameter or install `ext-gd`.
- **Negative timeout / retry**: every numeric parameter is validated in
  the matching value-object constructor (see
  [`configuration.md`](configuration.md)).

The original exception is chained — read `$exception->getPrevious()` for
the precise reason.

## "No Selenium session is currently active"

`Daycry\PHPUnit\Selenium\Exception\SessionNotFoundException`. Two
common reasons:

1. You called `$this->browser()` or `$this->selenium()` (from
   `SeleniumAware`) inside a test that isn't decorated with
   `#[UseSelenium]`. The session is only created when the attribute is
   present on the test method or any class in its hierarchy.
2. You called the helpers from outside a test method (e.g. in a
   data provider or a static `setUpBeforeClass` hook). The session is
   bound to the running test, not to fixtures that run before/after it.

## "Wait timed out after Xms"

`Daycry\PHPUnit\Selenium\Wait\WaitTimeoutException`. Use the
`withMessage()` builder method to attach context:

```php
$this->browser()->wait()
    ->forElement(Locator::testId('dashboard'))
    ->timeout(10)
    ->withMessage('Dashboard never rendered after login')
    ->run();
```

The previous exception (the last error captured during polling) is chained
when available — typically a `NoSuchElementException` or
`StaleElementReferenceException`. See [`waits.md`](waits.md) for the full
reference.

## "Failed to capture screenshot"

`Daycry\PHPUnit\Selenium\Exception\ScreenshotException`. Either the
underlying WebDriver call failed (network blip, browser crash) or the
output directory is not writable. The exception message includes the
filename and the chained driver error.

## "Allure attachments do not appear"

The integration is opt-in. Make sure:

1. `composer require --dev allure-framework/allure-phpunit` was executed
   in your project.
2. `allure=true` (or `SELENIUM_ALLURE=true`) is set.
3. The Allure result directory (`report-path`) is writable.

When the package is missing, `AllureReporter::isAvailable()` returns
`false` and the calls become no-ops. See
[`observability.md`](observability.md#allure).

## "Element click intercepted" / flaky clicks

Modern UI animations frequently cover elements right after navigation.
Three escalations:

1. Wait for visibility before clicking:
   ```php
   $this->browser()->wait()->forVisible(Locator::testId('cta'))->run();
   $this->browser()->click(Locator::testId('cta'));
   ```
2. Increase the retry budget for that test:
   ```php
   #[UseSelenium(retryAttempts: 5)]
   ```
3. Globally raise it in `phpunit.xml`:
   ```xml
   <parameter name="retry-max-attempts" value="5"/>
   ```

## Tests pass locally but fail on CI

- **Selenium hub not reachable**: confirm the Grid container is healthy
  (`curl -fsSL http://localhost:4444/status`).
- **Race conditions / flakiness**: increase `retry-max-attempts` and
  timeouts via env vars rather than touching the tests.
- **Screenshots empty**: make sure the runner has write permission on the
  configured `screenshot-path` and that the directory is uploaded as an
  artifact (see the reference setup in
  [`integration.yml`](../.github/workflows/integration.yml)).
- **File uploads fail**: when the test runner and the browser run in
  separate containers you need a `LocalFileDetector`. See
  [`forms.md`](forms.md#file-uploads-through-selenium-grid).

## "PHP 8.2 ParseError: unexpected identifier `DEFAULT_…`"

You are running PHP 8.2 but consuming a build that uses PHP 8.3-only
typed class constants (`public const string …`). Update to the latest
release of this library; the public source code targets `^8.2`.

## "Class Qameta\\Allure\\Allure not found"

Either the `allure-framework/allure-phpunit` package is not installed or
your autoloader is stale. Install the package as described under
[Allure attachments do not appear](#allure-attachments-do-not-appear)
and run `composer dump-autoload`.

## Multiple browsers in parallel

Each test is bound to its own `SeleniumSession` keyed by `Test::id()`, so
data providers and parallel runners don't clash. Fan out across browsers
by setting `SELENIUM_BROWSER` per CI matrix row, or by stacking
`#[UseSelenium(browser: 'firefox')]` on individual methods.

## "PHPStan: Call to static method on an unknown class Qameta\\Allure\\Allure"

Install `allure-framework/allure-phpunit` as a dev dependency in your
project, even when you do not plan to enable Allure at runtime. PHPStan
needs the class definition during analysis. Alternatively, copy the
`ignoreErrors` snippet from this repo's
[`phpstan.neon.dist`](../phpstan.neon.dist) into your own configuration.

## Where to look next

- [Architecture overview](architecture.md) — the lifecycle, the service
  graph, the layers and their responsibilities.
- [Configuration reference](configuration.md) — every knob the extension
  accepts.
- [Observability](observability.md) — logging events, screenshot naming
  conventions, Allure attachments.
