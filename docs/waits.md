# Waits

Selenium tests are asynchronous: clicks trigger XHRs, animations, route
changes. The library exposes a fluent `WaitBuilder` to express "wait until
this is true" without sprinkling `sleep()` calls.

The builder is **immutable**: every modifier returns a new instance, so
sharing a base builder between tests is safe.

## Quick path

If you just need to wait for an element to be present, use the shortcut on
`Browser`:

```php
$this->browser()->waitFor(Locator::testId('dashboard'));
$this->browser()->waitFor(Locator::testId('dashboard'), timeoutSeconds: 10);
```

## Full builder

```php
$this->browser()->wait()
    ->forElement(Locator::id('dashboard'))
    ->timeout(10)            // seconds
    ->pollEvery(200)         // milliseconds
    ->withMessage('Dashboard never rendered')
    ->run();
```

`run()` is the only terminator; everything else returns a new builder.

## Conditions

| Method                                  | Condition                                          |
|-----------------------------------------|----------------------------------------------------|
| `forElement(Locator)`                   | Element is present in the DOM                      |
| `forElementGone(Locator)`               | Element is not present (or no longer matched)      |
| `forVisible(Locator)`                   | Element is present **and** displayed               |
| `forHidden(Locator)`                    | Element is not visible                             |
| `forText(string, ?Locator $within)`     | Substring is present (in body or a scoped element) |
| `forUrl(string, bool $contains = true)` | URL equals or contains the given fragment          |
| `forTitle(string, bool $contains = true)`| Title equals or contains the given fragment       |
| `forAlert()`                            | A native `alert/confirm/prompt` is open            |
| `forFunction(Closure)`                  | Custom predicate `(RemoteWebDriver $d): bool`      |

## Custom conditions

`forFunction()` is the escape hatch when none of the built-ins fit:

```php
use Facebook\WebDriver\Remote\RemoteWebDriver;

$this->browser()->wait()
    ->forFunction(function (RemoteWebDriver $d): bool {
        return $d->executeScript('return document.readyState') === 'complete';
    })
    ->timeout(15)
    ->withMessage('Document never reached readyState=complete')
    ->run();
```

## Modifiers

| Modifier                | Default            | Effect                                        |
|-------------------------|--------------------|-----------------------------------------------|
| `timeout(int $seconds)` | 30 s               | Total budget for the wait                     |
| `timeoutMs(int $ms)`    | 30 000 ms          | Same as `timeout()` but in milliseconds       |
| `pollEvery(int $ms)`    | 250 ms             | Sleep between polls                           |
| `withMessage(string)`   | derived from cond  | Custom message included in `WaitTimeoutException` |

The default timeout comes from `TimeoutConfig::defaultExplicitWaitMs`. You
can override it globally via the `timeout-explicit-ms` parameter / env
var, per test through `#[UseSelenium(timeoutSeconds: 60)]`, or per call
with `timeout()`.

## Failure mode

When the wait expires, `WaitBuilder` throws a
`Daycry\PHPUnit\Selenium\Wait\WaitTimeoutException`. The previous error
captured during polling (typically a `NoSuchElementException` or
`StaleElementReferenceException`) is chained as `getPrevious()` to ease
debugging.

```text
Wait timed out after 10000ms: Element id=dashboard not present
Caused by Facebook\WebDriver\Exception\NoSuchElementException: …
```

## Combining with assertions

Waits and assertions complement each other:

```php
$this->browser()->click(Locator::testId('publish'));
$this->browser()->wait()->forText('Published')->run();

$this->assertVisible(Locator::testId('publish-success-banner'));
$this->assertHidden(Locator::testId('publish-spinner'));
```

Wait for the *signal* of completion, assert the *consequences*. This
pattern keeps tests deterministic and readable.
