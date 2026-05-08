# Changelog

All notable changes to this project are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [2.0.0] - 2026-05-08

Major release. The full migration guide lives in
[`UPGRADE-2.0.md`](UPGRADE-2.0.md); this entry summarises the changes by
category.

### ⚠️ Breaking changes

- **Static `SeleniumDriver` singleton no longer wired by default.** The new
  `SeleniumExtension::bootstrap()` registers the v2 subscriber set, which
  drives sessions through `Session\SessionManager` and per-test
  `Session\SeleniumSession` instances. Tests that called
  `SeleniumDriver::getDriver()` directly must migrate to
  `$this->selenium()->driver()` (low-level) or `$this->browser()`
  (high-level facade) via the `Session\SeleniumAware` trait.
- **`SeleniumExtension::bootstrap()` now throws `ExtensionBootstrapException`
  on failure** instead of printing to stdout and calling `exit`. Custom
  PHPUnit reporters that scraped the previous output will need to look at
  the exception instead.
- **`SeleniumActions::takeScreenshot()` now throws `ScreenshotException`**
  instead of silently swallowing the underlying `WebDriverException`.
- **`Browser` enum renamed to `BrowserType`.** The enum still lives in
  `Daycry\PHPUnit\Selenium\Browser\` but `Browser` is now the user-facing
  facade class. Direct consumers of the enum (e.g. custom
  `BrowserDriverFactory` implementations) must update their imports.
- **Old `Subscribers\ConfigurationSubscriber` 11-positional-argument
  constructor is no longer registered.** Configuration is now produced by
  `Config\Loader\ConfigLoader` from typed `ConfigSource`s. Custom
  subclasses of the v1 subscriber will not survive — implement a
  `ConfigSource` instead.
- **`SeleniumAssertions::browser()` is now `abstract`.** Concrete
  implementations are provided by `SeleniumAware` (tests) and `Page`
  (Page Objects). Combining the trait with `SeleniumAware` no longer
  causes a method conflict; standalone usage must declare its own
  `browser()` method.

### Added

#### Foundations

- **Browser abstraction**: `Browser\BrowserType` enum (Chrome, Firefox,
  Edge, Safari), `BrowserDriverFactory` interface,
  `BrowserFactoryRegistry`, plus dedicated `ChromeDriverFactory`,
  `FirefoxDriverFactory`, `EdgeDriverFactory`, `SafariDriverFactory`
  with typed `BrowserCapabilities` value objects.
- **Typed configuration tree**: `Config\SeleniumConfig`, `BrowserConfig`,
  `RemoteEndpoint`, `TimeoutConfig`, `RetryConfig`, `ScreenshotConfig`,
  `ReportingConfig`, `ResolvedConfig` — all `readonly`.
- **Layered configuration loader**: `Config\Loader\ConfigSource` interface
  with `ArrayConfigSource`, `EnvConfigSource`, `XmlConfigSource`
  implementations and a `ConfigLoader` that merges them by priority
  (env > XML > defaults).
- **PSR-11 service container**: `Container\ServiceContainer` and
  `Container\ContainerBuilder` to wire services per test run; usable
  standalone in custom bootstraps.
- **Session lifecycle**: `Session\SessionManager`, `SeleniumSession`,
  `SeleniumContext` (scoped per-test accessor), `SeleniumAware` trait,
  `WebDriverFactoryInterface` and `DefaultWebDriverFactory` (enables
  mocking of the WebDriver in unit tests).
- **Action runners**: `Action\ActionRunner` interface with
  `WebDriverRunner`, `RetryingRunner` (decorator), `LoggingRunner`
  (PSR-3 decorator).
- **Retry policy**: `Retry\RetryPolicy` with exponential backoff + jitter,
  `Clock` interface and `SystemClock` implementation.

#### User-facing API

- **`Browser\Browser` chainable facade**: `visit`, `click`, `type`,
  `clear`, `fill`, `check`, `uncheck`, `select`, `hover`, `dragTo`,
  `scrollTo`, `upload`, `pressKey`, `find`, `findAll`, `exists`,
  `currentUrl`, `title`, `pageSource`, `refresh`, `back`, `forward`,
  `resize`, `maximize`, `executeScript`, `wait`, `waitFor`,
  `withinFrame`, `acceptAlert`, `dismissAlert`, `getAlertText`,
  `cookies`, `localStorage`, `sessionStorage`, `fillForm`.
- **`Browser\Element`**: immutable element handle with `text`,
  `attribute`, `value`, `isDisplayed`, `isEnabled`, `isSelected`,
  `tagName`, `classes`, `hasClass`, `remote`.
- **`Browser\Key` enum**: typed mapping over `WebDriverKeys` constants
  (`Enter`, `Tab`, `Escape`, `ArrowUp`, …).
- **`Locator\Locator`** typed value object with factory methods (`id`,
  `css`, `xpath`, `name`, `className`, `tagName`, `linkText`,
  `partialLinkText`, `testId`, `text`, `role`), `LocatorStrategy` enum
  and `LocatorResolver` translating to `WebDriverBy` with proper XPath
  escaping and a configurable test-id attribute.
- **`Wait\WaitBuilder`** fluent immutable wait API: `forElement`,
  `forElementGone`, `forVisible`, `forHidden`, `forText`, `forUrl`,
  `forTitle`, `forFunction`, `forAlert`, with `timeout`, `timeoutMs`,
  `pollEvery`, `withMessage` modifiers.
- **`Wait\WaitTimeoutException`** chains the previous error captured
  during polling.
- **`Asserts\SeleniumAssertions`** trait with 21 Selenium-aware
  assertions backed by PHPUnit's `Assert`: `assertVisible`,
  `assertHidden`, `assertExists`, `assertMissing`, `assertCount`,
  `assertText`, `assertTextContains`, `assertTextMatches`,
  `assertAttribute`, `assertHasClass`, `assertValue`, `assertChecked`,
  `assertNotChecked`, `assertEnabled`, `assertDisabled`, `assertUrlIs`,
  `assertUrlContains`, `assertUrlMatches`, `assertTitle`,
  `assertTitleContains`, `assertCookie`.
- **Page Object base**: `Page\Page` (with `url`, `assertOnPage`, `visit`)
  and `Page\Component` (scoped by a root locator).
- **Form helpers**: `Form\Select`, `Form\Upload`, `Form\Date` value
  objects + `Browser::fillForm()` dispatcher.
- **Storage wrappers**: `Storage\CookieJar` and `Storage\Storage` for
  cookies and `localStorage` / `sessionStorage`.
- **Extended `#[UseSelenium]` attribute**: optional named arguments
  `browser`, `profile`, `timeoutSeconds`, `pageLoadTimeoutMs`,
  `retryAttempts`, `screenshot`, `capabilities`, `browserVersion`,
  `platform`, `tags`. Marked `IS_REPEATABLE` for class-hierarchy
  layering. The bare `#[UseSelenium]` form keeps working unchanged.
- **`Attribute\Resolver`**: `TestAttributeResolver` walks the class
  hierarchy parents-first, supports repeatable attributes, caches
  results per `class::method`. `ResolvedAttributes` and
  `AttributeOverlay` produce per-test `ResolvedConfig`.
- **`SeleniumAware::browser()`** helper returning a cached `Browser`
  facade for the current test, alongside the existing `selenium()`
  accessor.

#### Observability

- **PSR-3 logging end-to-end**: events emitted by every subscriber and
  by `LoggingRunner` (bootstrap, session start/closed, action ok/fail,
  retry attempt, screenshot saved, test failed, leaked sessions). The
  default logger is a `NullLogger`; replaceable through the container.
- **Screenshots with metadata**: `Screenshot\FilenameSanitizer` produces
  filenames of the form
  `{ISO8601}_{class}_{method}_{browser}_{status}_{shortSession}.{ext}`.
  `Screenshot\ScreenshotService` honours the `off`, `on-failure`,
  `every-step` modes and the `png`, `webp` formats.
- **Allure integration**: `Reporting\AllureReporter` (no-op fallback when
  `allure-framework/allure-phpunit` is absent) attaches the failure
  screenshot and the browser console log to Allure, plus
  `Reporting\BrowserLogCollector` for the underlying log retrieval.
- **Subscribers**: `Subscriber\BootstrapSubscriber`, `StartTestSubscriber`,
  `FailedTestSubscriber`, `FinishTestSubscriber`, `ShutdownSubscriber`.

#### Tooling

- **Mutation testing**: `infection.json5` with MSI ≥ 70 (covered MSI ≥ 80)
  and a dedicated `infection.yml` workflow.
- **Architecture fitness**: `deptrac.yaml` with rules per layer
  (`Attribute / Browser / Locator / Wait / Asserts / Page / Form /
  Storage / Action / Retry / Config / Container / Session / Screenshot
  / Reporting / Subscriber / Extension / Exception`).
- **Dependency automation**: `renovate.json` with auto-merge for patch
  dev-deps after CI green and grouped GitHub Actions updates.
- **Focused CI workflows** (one badge → one signal):
  `phpunit.yml` (matrix PHP 8.2/8.3/8.4 × PHPUnit ^10/^11, Codecov upload),
  `phpstan.yml`, `rector.yml`, `code-style.yml`, `infection.yml`,
  `integration.yml` (nightly + PR-label E2E on a real Selenium hub),
  `release.yml` (validate + GitHub Release with SBOM).

#### Documentation

- New `docs/` set: `index.md` (TOC), `getting-started.md`,
  `configuration.md`, `browsers.md`, `page-objects.md`, `asserts.md`,
  `waits.md`, `forms.md`, `storage.md`, `observability.md`,
  `architecture.md`, `troubleshooting.md`, `migration-v1-to-v2.md`.
- Project root: `UPGRADE-2.0.md` with the full v1 → v2 migration table.
- README rewritten in three badge sections (Package / Quality /
  Community), TOC, install + configure + first-test walkthrough,
  capability matrix linking every doc page.
- `CONTRIBUTING.md` updated with the toolchain table, conventional
  commits guidance and architectural rules.

#### Dependencies

- `psr/container` `^1.1 || ^2.0` and `psr/log` `^2.0 || ^3.0` declared
  as direct runtime requirements.
- `allure-framework/allure-phpunit` `^3.1` declared as `require-dev`
  (so PHPStan can analyse `Reporting\AllureReporter`) and surfaced via
  `suggest` for end users.

### Changed

- `SeleniumExtension::bootstrap()` now builds a service container via
  `Container\ContainerBuilder` and registers the v2 subscriber set.
- README badges restructured into three sections (Package / Quality /
  Community) with Codecov, Infection and per-tool workflow badges.
- `phpstan.neon.dist` pins `phpVersion: 80200` so post-8.2 syntax is
  flagged at analysis time instead of at runtime.

### Fixed

- `SeleniumExtension::bootstrap()` no longer prints to stdout and calls
  `exit` on failure (carried over from 1.1.0 and verified for v2.0).
- `SeleniumActions::takeScreenshot()` no longer swallows
  `WebDriverException` (carried over from 1.1.0).
- Removed PHP 8.3-only typed class constants (`public const string`,
  `public const int`, etc.) so the codebase parses on PHP 8.2 — affected
  `RemoteEndpoint`, `TimeoutConfig`, `RetryConfig`, `EnvConfigSource`,
  `XmlConfigSource`, `ContainerBuilder`, `Form\Select`.
- Replaced the brittle `$reflection->getProperty('className')` reflection
  trick in attribute resolution with the public
  `TestMethod::className()` API.

### Migration

- See [`UPGRADE-2.0.md`](UPGRADE-2.0.md) for the full guide and
  [`docs/migration-v1-to-v2.md`](docs/migration-v1-to-v2.md) for a quick
  lookup table.
- The v1 `Libraries\SeleniumDriver`, `Traits\SeleniumActions` and
  `Subscribers\*` classes are kept in the source tree as transitional
  artefacts but are no longer registered by default. They will be
  removed in 3.0.

## [1.1.0] - 2026-05-08

### Added
- Initial test suite scaffolding under `tests/Unit`.
- `phpunit.xml.dist` shipped with the package.
- `phpstan.neon.dist` baseline at level 6.
- `rector.php` configuration (dry-run by default).
- `Daycry\PHPUnit\Selenium\Exception\SeleniumException` base exception and dedicated
  `ExtensionBootstrapException` and `ScreenshotException` subclasses.
- Project governance documents: `CONTRIBUTING.md`, `SECURITY.md`, `CODE_OF_CONDUCT.md`.
- Minimal GitHub Actions CI workflow (`.github/workflows/ci.yml`).

### Changed
- `SeleniumExtension::bootstrap()` now throws `ExtensionBootstrapException` instead of
  printing to stdout and calling `exit`, allowing PHPUnit to surface bootstrap failures
  through its normal reporting channels.
- `SeleniumActions::takeScreenshot()` no longer swallows `WebDriverException`; failures
  now raise `ScreenshotException` with the original exception chained as `previous`.

### Deprecated
- The static `SeleniumDriver` singleton is marked for removal in `2.0.0`. A migration
  path will be provided through a separate `*-compat` package.

## [1.0.0] - 2025

### Added
- Initial public release with `#[UseSelenium]` attribute, lazy WebDriver bootstrap,
  on-failure screenshots, and the `SeleniumActions` trait.

[Unreleased]: https://github.com/daycry/phpunit-extension-selenium/compare/v2.0.0...HEAD
[2.0.0]: https://github.com/daycry/phpunit-extension-selenium/compare/v1.1.0...v2.0.0
[1.1.0]: https://github.com/daycry/phpunit-extension-selenium/compare/v1.0.0...v1.1.0
[1.0.0]: https://github.com/daycry/phpunit-extension-selenium/releases/tag/v1.0.0
