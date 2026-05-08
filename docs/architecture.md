# Architecture

This page is for contributors and advanced users who want to understand
how `SeleniumExtension` wires everything together at runtime.

## Layers

The codebase is split into single-responsibility namespaces. Architectural
rules are enforced by [Deptrac](../deptrac.yaml):

```
Attribute   ─┐                ┌─→ Browser
Asserts ────┼─→ Browser ───→ Action ─→ Retry
Page    ───┘                  │     ↓
                              ↓     Config
Locator   ─→ Wait             │
                              ↓
                              Session ─→ Browser
                              │
                              ↓
                              Container ─→ Reporting / Screenshot

Subscriber ─→ Attribute / Config / Reporting / Screenshot / Session
SeleniumExtension ─→ Container + Subscriber

Exception is a leaf used by everyone.
```

## Service graph

`SeleniumExtension::bootstrap()` builds a PSR-11 service container via
`Container\ContainerBuilder` and registers five subscribers. The graph:

```
ServiceContainer
├─ selenium.config              SeleniumConfig (loaded from sources)
│   ├─ RemoteEndpoint           host, connect/request timeouts
│   ├─ BrowserConfig            BrowserType + BrowserCapabilities
│   ├─ TimeoutConfig            implicit / pageLoad / script / explicit
│   ├─ RetryConfig              attempts, backoff, jitter, retryable[]
│   ├─ ScreenshotConfig         enabled, path, mode, format
│   └─ ReportingConfig          allure, reportPath, video, console
├─ Psr\Log\LoggerInterface      NullLogger by default
├─ WebDriverFactoryInterface    DefaultWebDriverFactory
├─ SessionManager               keyed by Test::id()
├─ TestAttributeResolver        cached per class::method
├─ ScreenshotService            FilenameSanitizer + ScreenshotConfig
├─ AllureReporter               no-op unless allure=true + package present
└─ BrowserLogCollector
```

`ContainerBuilder` is exposed publicly so consumers can build a customised
container in their own bootstrap and swap services (a custom logger, a
mock `WebDriverFactoryInterface`, …).

## Configuration loading

Three sources, ordered by `priority()`:

| Source                     | Priority | Origin                           |
|----------------------------|----------|----------------------------------|
| `EnvConfigSource`          | 100      | `SELENIUM_*` env variables       |
| `XmlConfigSource`          | 50       | `<parameter>` entries            |
| `ArrayConfigSource` (defaults) | 0    | Library defaults                 |

`ConfigLoader` merges them in priority order — higher priority overrides
lower. The output is a fully-typed `SeleniumConfig`. Validation happens in
the value-object constructors (URL validity, positive timeouts, writable
screenshot paths, valid retry parameters).

For per-test overrides, `AttributeOverlay::apply()` produces a
`ResolvedConfig` by layering `#[UseSelenium(...)]` on top of the base
`SeleniumConfig`. The resolver caches its work per `class::method`.

## Test lifecycle

The extension reacts to PHPUnit events through dedicated subscribers, all
under `Daycry\PHPUnit\Selenium\Subscriber`:

```
TestRunner\ExecutionStarted        BootstrapSubscriber
                                   ├─ binds SessionManager into SeleniumContext
                                   └─ logs selenium.bootstrap

Test\Prepared (per test)           StartTestSubscriber
                                   ├─ resolves UseSelenium attributes
                                   ├─ applies them on the base config
                                   ├─ creates a SeleniumSession
                                   ├─ pushes it onto SeleniumContext
                                   └─ starts the driver

Test\Failed                        FailedTestSubscriber
                                   ├─ captures a screenshot
                                   ├─ attaches screenshot + console logs to Allure
                                   └─ logs selenium.test.failed

Test\Finished                      FinishTestSubscriber
                                   ├─ pops from SeleniumContext
                                   └─ closes the session (driver.quit)

TestRunner\ExecutionFinished       ShutdownSubscriber
                                   └─ defensive closeAll() for leaked sessions
```

If a test method does not carry `#[UseSelenium]` (directly or via a parent
class), `StartTestSubscriber` does nothing — there's zero driver overhead
for the rest of your suite.

## `SeleniumContext`

`SeleniumContext` is a scoped, stack-based session accessor. `static`
storage is fine here because each test is push/pop within a single PHP
process — there is no global mutation across tests. The stack also handles
the (rare) case of nested fixtures or sub-tests properly.

`SeleniumContext::current()` is what `SeleniumAware::selenium()` and
`SeleniumAware::browser()` read.

## Action runners

Every state-mutating call inside `Browser` goes through an
`Action\ActionRunner`. The default chain is:

```
LoggingRunner  →  RetryingRunner  →  WebDriverRunner
```

- `LoggingRunner` emits PSR-3 events with timing info.
- `RetryingRunner` wraps `RetryPolicy` to retry on
  `StaleElementReferenceException`, `NoSuchElementException`,
  `ElementNotInteractableException` (configurable).
- `WebDriverRunner` is the leaf — calls the underlying webdriver method.

You can swap the chain by injecting a different `ActionRunner` in your
custom bootstrap.

## Locator resolution

`Locator` is a typed value object; `LocatorResolver` translates it to the
underlying `WebDriverBy`. Strategy mapping:

| `LocatorStrategy` | `WebDriverBy`                 | Notes                                |
|-------------------|-------------------------------|--------------------------------------|
| `Id`              | `WebDriverBy::id`             | direct                               |
| `Css`             | `WebDriverBy::cssSelector`    | direct                               |
| `XPath`           | `WebDriverBy::xpath`          | direct                               |
| `Name`            | `WebDriverBy::name`           | direct                               |
| `ClassName`       | `WebDriverBy::className`      | direct                               |
| `TagName`         | `WebDriverBy::tagName`        | direct                               |
| `LinkText`        | `WebDriverBy::linkText`       | direct                               |
| `PartialLinkText` | `WebDriverBy::partialLinkText`| direct                               |
| `TestId`          | `WebDriverBy::cssSelector`    | `[data-testid="<value>"]` (configurable attribute) |
| `Text`            | `WebDriverBy::xpath`          | `//*[normalize-space(text())=…]`     |
| `Role`            | `WebDriverBy::cssSelector`    | `[role="<value>"]`                   |

`TestId` and `Role` synthesise CSS selectors. `Text` uses XPath with
correct quote escaping (single, double, mixed).

## Extending the library

Common extension points:

| Need                                  | Hook                                              |
|---------------------------------------|---------------------------------------------------|
| Custom logger                         | `$container->instance(LoggerInterface::class, …)` |
| Mock WebDriver in tests               | implement `WebDriverFactoryInterface`             |
| Add config sources                    | implement `ConfigSource`                          |
| Replace retry behaviour               | wrap a different `ActionRunner`                   |
| Different test-id attribute           | `LocatorResolver(testIdAttribute: 'data-test')`   |
| New browser support                   | implement `BrowserDriverFactory` and register it  |
