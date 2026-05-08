# Troubleshooting

## "Failed to bootstrap extension"

This `ExtensionBootstrapException` wraps the underlying configuration
error. Common causes:

- **Invalid host URL**: `host` (or `SELENIUM_HOST`) must be a valid URL.
- **Unwritable screenshot path**: when `screenshot=true`, the parent of
  `screenshot-path` must exist and be writable.
- **Unsupported browser**: `browser-name` must be `chrome`, `firefox`,
  `edge` or `safari`.

The original exception is chained — read `getPrevious()` for the precise
reason.

## "No Selenium session is currently active"

You called `$this->selenium()` (via `SeleniumAware`) inside a test that
isn't decorated with `#[UseSelenium]`. The session is only created when the
attribute is present on the test method or any class in its hierarchy.

## "Wait timed out after Xms"

`WaitTimeoutException`. Use the `withMessage()` builder method to attach
context:

```php
$browser->wait()
    ->forElement(Locator::testId('dashboard'))
    ->timeout(10)
    ->withMessage('Dashboard never rendered after login')
    ->run();
```

The previous exception (the last error captured during polling) is chained
when available.

## Tests pass locally but fail on CI

- **Selenium hub not reachable**: confirm the Grid container is healthy
  (`curl -fsSL http://localhost:4444/status`).
- **Race conditions**: increase `retry-max-attempts` and timeouts via env
  vars rather than touching the tests.
- **Screenshots empty**: make sure the runner has write permission on the
  configured `screenshot-path`.

## Multiple browsers in parallel

Each test is bound to its own `SeleniumSession` keyed by test id, so data
providers and parallel runners don't clash. Set `SELENIUM_BROWSER` per CI
matrix row to fan out across Chrome/Firefox/Edge.
