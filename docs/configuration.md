# Configuration

Configuration is layered: built-in defaults < `phpunit.xml` parameters < env vars.
Anything you set in env wins over XML, which wins over defaults. This lets a
single test suite target different environments without changes.

> The full architecture of the configuration loader is described in
> [`architecture.md`](architecture.md#configuration-loading).

## Quick start

The shortest useful configuration:

```xml
<extensions>
    <bootstrap class="Daycry\PHPUnit\Selenium\SeleniumExtension">
        <parameter name="host"         value="http://localhost:4444/wd/hub"/>
        <parameter name="browser-name" value="chrome"/>
    </bootstrap>
</extensions>
```

Anything you don't set falls back to the defaults shown in the next section.

## All XML parameters

Everything is optional.

```xml
<extensions>
    <bootstrap class="Daycry\PHPUnit\Selenium\SeleniumExtension">
        <parameter name="host"                   value="http://localhost:4444/wd/hub"/>
        <parameter name="browser-name"           value="chrome"/>
        <parameter name="browser-version"        value="latest"/>
        <parameter name="platform-name"          value="linux"/>
        <parameter name="page-load-strategy"     value="normal"/>
        <parameter name="user-agent"             value="MyTestRunner/1.0"/>
        <parameter name="accept-insecure-certs"  value="true"/>
        <parameter name="options"                value="--start-maximized,--disable-infobars"/>

        <parameter name="timeout-page-load-ms"   value="30000"/>
        <parameter name="timeout-script-ms"      value="30000"/>
        <parameter name="timeout-implicit-ms"    value="0"/>
        <parameter name="timeout-explicit-ms"    value="30000"/>

        <parameter name="retry-max-attempts"     value="3"/>
        <parameter name="retry-initial-delay-ms" value="100"/>
        <parameter name="retry-multiplier"       value="2.0"/>

        <parameter name="screenshot"             value="true"/>
        <parameter name="screenshot-path"        value="build/screenshots"/>
        <parameter name="screenshot-mode"        value="on-failure"/>
        <parameter name="screenshot-format"      value="png"/>

        <parameter name="allure"                 value="false"/>
        <parameter name="report-path"            value="build/allure-results"/>
        <parameter name="video-enabled"          value="false"/>
    </bootstrap>
</extensions>
```

### What each parameter does

| Parameter                  | Type             | Default                                    | Notes                                                                 |
|----------------------------|------------------|--------------------------------------------|-----------------------------------------------------------------------|
| `host`                     | URL              | `http://localhost:4444/wd/hub`             | Selenium hub or standalone endpoint                                   |
| `browser-name`             | enum             | `chrome`                                   | `chrome` / `firefox` / `edge` / `safari`                              |
| `browser-version`          | string           | (unset)                                    | Forwarded as `browserVersion` capability                              |
| `platform-name`            | string           | `linux`                                    | Forwarded as `platformName` capability                                |
| `page-load-strategy`       | string           | (unset)                                    | `normal`, `eager`, `none`                                             |
| `user-agent`               | string           | (unset)                                    | Custom UA string                                                      |
| `accept-insecure-certs`    | bool             | `true`                                     | Allow self-signed certs                                               |
| `options`                  | comma-separated  | `--start-maximized,--disable-infobars,--disable-extensions` | Browser flags                                          |
| `timeout-page-load-ms`     | int              | `30000`                                    | Selenium pageLoad timeout                                             |
| `timeout-script-ms`        | int              | `30000`                                    | Selenium script timeout                                               |
| `timeout-implicit-ms`      | int              | `0`                                        | Selenium implicit wait                                                |
| `timeout-explicit-ms`      | int              | `30000`                                    | Default `WaitBuilder` budget                                          |
| `retry-max-attempts`       | int              | `1`                                        | Retries per action on `RetryConfig::retryableExceptions`              |
| `retry-initial-delay-ms`   | int              | `100`                                      | Initial backoff delay                                                 |
| `retry-multiplier`         | float            | `2.0`                                      | Exponential growth factor                                             |
| `screenshot`               | bool             | `false`                                    | Master switch                                                         |
| `screenshot-path`          | path             | `sys_get_temp_dir()/selenium-screenshots`  | Output directory                                                      |
| `screenshot-mode`          | enum             | `on-failure`                               | `off`, `on-failure`, `every-step`                                     |
| `screenshot-format`        | enum             | `png`                                      | `png`, `webp` (requires `ext-gd`)                                     |
| `allure`                   | bool             | `false`                                    | Enable Allure attachments (needs `allure-framework/allure-phpunit`)   |
| `report-path`              | path             | (unset)                                    | Output directory for Allure results                                   |
| `video-enabled`            | bool             | `false`                                    | Set `se:recordVideo=true` on the capabilities                         |

## All environment variables

Env vars override the matching XML parameter:

```
SELENIUM_HOST                      # http://localhost:4444/wd/hub
SELENIUM_BROWSER                   # chrome | firefox | edge | safari
SELENIUM_BROWSER_VERSION
SELENIUM_PLATFORM
SELENIUM_USER_AGENT
SELENIUM_PAGE_LOAD_STRATEGY
SELENIUM_ACCEPT_INSECURE_CERTS     # true | false
SELENIUM_OPTIONS                   # comma-separated browser flags
SELENIUM_PROFILE                   # named profile selector

SELENIUM_TIMEOUT_PAGE_LOAD_MS
SELENIUM_TIMEOUT_SCRIPT_MS
SELENIUM_TIMEOUT_IMPLICIT_MS
SELENIUM_TIMEOUT_EXPLICIT_MS

SELENIUM_RETRY_MAX_ATTEMPTS
SELENIUM_RETRY_INITIAL_DELAY_MS
SELENIUM_RETRY_MULTIPLIER

SELENIUM_SCREENSHOT                # true | false
SELENIUM_SCREENSHOT_PATH
SELENIUM_SCREENSHOT_MODE           # off | on-failure | every-step
SELENIUM_SCREENSHOT_FORMAT         # png | webp

SELENIUM_ALLURE                    # true | false
SELENIUM_REPORT_PATH
SELENIUM_VIDEO_ENABLED
```

Typical CI usage — pin the host in XML, fan out the browser via env in the
matrix:

```bash
SELENIUM_BROWSER=firefox SELENIUM_HOST=http://grid:4444/wd/hub vendor/bin/phpunit
```

## Per-test overrides

Use `#[UseSelenium]` named arguments to override the resolved configuration
for a single test:

```php
use Daycry\PHPUnit\Selenium\Attributes\UseSelenium;

#[UseSelenium(
    browser:           'firefox',
    profile:           'mobile',
    timeoutSeconds:    60,
    pageLoadTimeoutMs: 15_000,
    retryAttempts:     5,
    screenshot:        true,
    capabilities:      [
        'goog:chromeOptions' => [
            'mobileEmulation' => ['deviceName' => 'Pixel 5'],
        ],
    ],
    browserVersion:    'latest',
    platform:          'linux',
    tags:              ['critical', 'smoke'],
)]
public function testFoo(): void { /* ... */ }
```

| Argument             | Type                            | Effect                                                |
|----------------------|---------------------------------|-------------------------------------------------------|
| `browser`            | `'chrome'\|'firefox'\|'edge'\|'safari'` | Switches browser for this test only           |
| `profile`            | `string`                        | Selects a named profile (see below)                   |
| `timeoutSeconds`     | `int`                           | Sets `defaultExplicitWaitMs` (= `timeoutSeconds * 1000`) |
| `pageLoadTimeoutMs`  | `int`                           | Overrides `timeout-page-load-ms`                      |
| `retryAttempts`      | `int`                           | Overrides `retry-max-attempts`                        |
| `screenshot`         | `bool`                          | Force-enable / force-disable for this test            |
| `capabilities`       | `array<string, scalar\|array>`  | Raw capability overlay (escape hatch)                 |
| `browserVersion`     | `?string`                       | Overrides `browser-version`                           |
| `platform`           | `?string`                       | Overrides `platform-name`                             |
| `tags`               | `list<string>`                  | Free-form labels surfaced through reporting           |

The attribute is `IS_REPEATABLE`. The merge order is **parents first**,
then the class itself, then the method — every non-null field wins over
the previous one. Tags and capabilities are union-merged.

## Profiles

A profile is a named overlay on top of the base configuration. They are
useful when the same suite runs in several modes (mobile / desktop, local
/ CI / staging) with diverging settings.

The default `ContainerBuilder` does not register any profile out of the
box. To add one, build a custom container in your own bootstrap:

```php
use Daycry\PHPUnit\Selenium\Browser\Capabilities\ChromeCapabilities;
use Daycry\PHPUnit\Selenium\Config\BrowserConfig;
use Daycry\PHPUnit\Selenium\Config\Loader\ArrayConfigSource;
use Daycry\PHPUnit\Selenium\Config\Loader\ConfigLoader;
use Daycry\PHPUnit\Selenium\Config\Loader\EnvConfigSource;
use Daycry\PHPUnit\Selenium\Config\Loader\XmlConfigSource;
use Daycry\PHPUnit\Selenium\Config\SeleniumConfig;
use Daycry\PHPUnit\Selenium\Container\ContainerBuilder;

$base = (new ConfigLoader([
    new XmlConfigSource($parameters),
    new EnvConfigSource(),
]))->load();

$mobile = new SeleniumConfig(
    endpoint: $base->endpoint,
    browser:  new BrowserConfig(
        browser:      $base->browser->browser,
        capabilities: new ChromeCapabilities(
            args: ['--window-size=375,667'],
            extra: [
                'goog:chromeOptions' => [
                    'mobileEmulation' => ['deviceName' => 'Pixel 5'],
                ],
            ],
        ),
    ),
);

$baseWithProfiles = new SeleniumConfig(
    endpoint:   $base->endpoint,
    browser:    $base->browser,
    timeouts:   $base->timeouts,
    retry:      $base->retry,
    screenshot: $base->screenshot,
    reporting:  $base->reporting,
    profiles:   ['mobile' => $mobile],
);
```

Tests then opt into the profile per method:

```php
#[UseSelenium(profile: 'mobile')]
public function testMobileLayout(): void { /* ... */ }
```

## Customising the wiring

For more advanced customisation (replacing the logger, mocking the
WebDriver factory, registering extra browsers), use `ContainerBuilder`
directly. See [`observability.md`](observability.md#psr-3-logging) and
[`architecture.md`](architecture.md#extending-the-library) for examples.
