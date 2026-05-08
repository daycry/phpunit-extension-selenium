# Changelog

All notable changes to this project are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added — DX and documentation pass

- `SeleniumAware` now exposes a `browser(): Browser` helper that lazily
  builds and caches the facade for the current test, so call sites become
  `$this->browser()->visit(...)` instead of
  `(new Browser($this->selenium()))->visit(...)`.
- New documentation pages under `docs/`: `forms.md` (`Select`, `Upload`,
  `Date`, `fillForm`), `storage.md` (cookies + local/session storage),
  `waits.md` (full `WaitBuilder` reference), `observability.md` (PSR-3
  events, screenshots, Allure, console logs, video), `architecture.md`
  (service graph, lifecycle, layers, extension points), `index.md` (TOC).
- README, getting-started, asserts, page-objects, migration guide and
  `UPGRADE-2.0.md` all rewritten to use the new `$this->browser()`
  shortcut and to cross-link the new doc pages.
- `CONTRIBUTING.md` updated with the full toolchain table (PHPUnit,
  PHPStan, Rector, php-cs-fixer, Infection, Deptrac, Renovate),
  conventional-commits guidance and architectural rules.

### Changed
- `SeleniumAssertions::browser()` is now declared `abstract`. Implementations
  (provided by `SeleniumAware` and `Page`) supply the Browser instance, so
  combining the trait with `SeleniumAware` no longer triggers a method
  conflict.

### Added — v2.0 user-facing API and runtime wiring

- `Daycry\PHPUnit\Selenium\Browser\Browser` chainable facade with typed
  `Locator` (id, css, xpath, name, className, tagName, linkText,
  partialLinkText, testId, text, role) and immutable `Element` query handle.
  Mutating commands return `Browser`, queries return values.
- `Daycry\PHPUnit\Selenium\Wait\WaitBuilder` fluent wait API with
  `forElement`, `forElementGone`, `forVisible`, `forHidden`, `forText`,
  `forUrl`, `forTitle`, `forFunction`, `forAlert`. `WaitTimeoutException`
  carries the previous error and a custom message.
- `Daycry\PHPUnit\Selenium\Asserts\SeleniumAssertions` trait with 20+
  Selenium-aware assertions backed by PHPUnit's `Assert` (`assertVisible`,
  `assertHidden`, `assertExists`, `assertMissing`, `assertCount`,
  `assertText`, `assertTextContains`, `assertTextMatches`, `assertAttribute`,
  `assertHasClass`, `assertValue`, `assertChecked`, `assertNotChecked`,
  `assertEnabled`, `assertDisabled`, `assertUrlIs`, `assertUrlContains`,
  `assertUrlMatches`, `assertTitle`, `assertTitleContains`, `assertCookie`).
- `Daycry\PHPUnit\Selenium\Page\Page` and `Daycry\PHPUnit\Selenium\Page\Component`
  base classes for the Page Object pattern.
- `Daycry\PHPUnit\Selenium\Form\Select`, `Upload`, `Date` value objects and
  `Browser::fillForm()` dispatcher.
- `Daycry\PHPUnit\Selenium\Storage\CookieJar` and
  `Daycry\PHPUnit\Selenium\Storage\Storage` wrappers for cookies, localStorage
  and sessionStorage.
- `Daycry\PHPUnit\Selenium\Browser\Key` enum mapping to `WebDriverKeys`.
- `Daycry\PHPUnit\Selenium\Screenshot\FilenameSanitizer` and
  `Daycry\PHPUnit\Selenium\Screenshot\ScreenshotService` producing
  `{ISO8601}_{class}_{method}_{browser}_{status}_{shortSession}.{ext}` names.
- `Daycry\PHPUnit\Selenium\Reporting\AllureReporter` (no-op when the optional
  `allure-framework/allure-phpunit` package is not installed) and
  `BrowserLogCollector` for browser/driver console logs.
- New v2 subscribers replace the v1 ones at runtime:
  `Subscriber\BootstrapSubscriber`, `StartTestSubscriber`,
  `FailedTestSubscriber`, `FinishTestSubscriber`, `ShutdownSubscriber`.
  `SeleniumExtension::bootstrap()` now wires them through a
  `Container\ContainerBuilder`.
- PSR-3 logging emitted at every lifecycle event (bootstrap, session
  start/closed, action ok/fail, retry attempt, screenshot saved, test
  failed, leaked sessions).
- Tooling configs: `infection.json5` (Infection mutation testing with
  MSI thresholds), `deptrac.yaml` (architectural fitness rules per layer),
  `renovate.json` (dependency automation with auto-merge for patch dev-deps).
- Two new GitHub Actions workflows:
  `.github/workflows/integration.yml` (nightly + label-triggered E2E run
  against Selenium standalone matrix on Chrome/Firefox/Edge with artifact
  upload) and `.github/workflows/release.yml` (validate + tag release
  with auto-generated notes and SBOM upload).
- Documentation set under `docs/`: `getting-started.md`, `configuration.md`,
  `browsers.md`, `page-objects.md`, `asserts.md`, `troubleshooting.md`,
  `migration-v1-to-v2.md`. Project root: `UPGRADE-2.0.md` with a full
  v1→v2 mechanical migration table.
- README rewritten: badges, highlights, quick example with the new API,
  link to docs.

### Changed
- `Daycry\PHPUnit\Selenium\Browser\Browser` was renamed to `BrowserType` to
  free the `Browser` name for the user-facing facade. The enum still lives
  in the `Browser\` namespace; only the class name changed.
- `SeleniumExtension::bootstrap()` now builds a service container via
  `Container\ContainerBuilder` and registers the v2 subscriber set. The
  v1 `Subscribers\*` classes still exist for backwards compatibility but
  are no longer registered by default.

### Added — v2.0 architecture (foundations from previous milestone)

- `Daycry\PHPUnit\Selenium\Browser` — `Browser` enum (Chrome/Firefox/Edge/Safari),
  `BrowserDriverFactory` interface and `BrowserFactoryRegistry`, plus dedicated
  `ChromeDriverFactory`, `FirefoxDriverFactory`, `EdgeDriverFactory`,
  `SafariDriverFactory`, with typed `BrowserCapabilities` value objects.
- `Daycry\PHPUnit\Selenium\Config` — typed configuration tree composed of
  readonly value objects (`SeleniumConfig`, `BrowserConfig`, `RemoteEndpoint`,
  `TimeoutConfig`, `RetryConfig`, `ScreenshotConfig`, `ReportingConfig`,
  `ResolvedConfig`) and a layered loader (`ConfigSource`, `ArrayConfigSource`,
  `EnvConfigSource`, `XmlConfigSource`, `ConfigLoader`) where env vars override
  XML parameters.
- `Daycry\PHPUnit\Selenium\Container\ServiceContainer` — minimal PSR-11 container
  used to wire services per test run.
- `Daycry\PHPUnit\Selenium\Session` — `SessionManager`, `SeleniumSession`,
  `SeleniumContext` (scoped per-test session accessor), `SeleniumAware` trait
  for tests, `WebDriverFactoryInterface` and `DefaultWebDriverFactory` to
  enable mock-driven testing of the session lifecycle.
- `Daycry\PHPUnit\Selenium\Action` — `ActionRunner` interface with
  `WebDriverRunner`, `RetryingRunner` (decorator) and `LoggingRunner` (PSR-3
  decorator) for per-action observability and flakiness handling.
- `Daycry\PHPUnit\Selenium\Retry` — `RetryPolicy` with exponential backoff +
  jitter, plus `Clock` interface and `SystemClock` implementation.
- `Daycry\PHPUnit\Selenium\Locator` — typed `Locator` value object with
  factory methods (`id`, `css`, `xpath`, `name`, `testId`, `text`, `role`, …),
  `LocatorStrategy` enum and `LocatorResolver` translating to `WebDriverBy`
  with proper escaping and a configurable test-id attribute.
- `Daycry\PHPUnit\Selenium\Attribute\Resolver` — `TestAttributeResolver` walks
  the class hierarchy parents-first, supports repeatable attributes, caches
  results per `class::method`. `ResolvedAttributes` and `AttributeOverlay`
  produce per-test `ResolvedConfig`.
- `#[UseSelenium]` extended with optional named arguments (`browser`, `profile`,
  `timeoutSeconds`, `pageLoadTimeoutMs`, `retryAttempts`, `screenshot`,
  `capabilities`, `browserVersion`, `platform`, `tags`) and marked
  `IS_REPEATABLE`. Existing `#[UseSelenium]` (no args) usage stays compatible.
- `psr/container` and `psr/log` declared as direct dependencies.
- 91-test internal suite covering the new foundations.

The new namespaces ship alongside the v1 runtime (`SeleniumDriver`,
`SeleniumActions`, current subscribers) so existing setups keep working until
the migration to v2.0 wires them in.

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

[Unreleased]: https://github.com/daycry/phpunit-extension-selenium/compare/v1.1.0...HEAD
[1.1.0]: https://github.com/daycry/phpunit-extension-selenium/compare/v1.0.0...v1.1.0
[1.0.0]: https://github.com/daycry/phpunit-extension-selenium/releases/tag/v1.0.0
