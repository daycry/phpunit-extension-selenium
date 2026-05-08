# Configuration

Configuration is layered: built-in defaults < `phpunit.xml` parameters < env vars.
Anything you set in env wins over XML, which wins over defaults. This lets a
single test suite target different environments without changes.

## XML parameters

Everything is optional. The defaults match the v1 behaviour where applicable.

```xml
<extensions>
    <bootstrap class="Daycry\PHPUnit\Selenium\SeleniumExtension">
        <parameter name="host" value="http://localhost:4444/wd/hub"/>
        <parameter name="browser-name" value="chrome"/>
        <parameter name="browser-version" value="latest"/>
        <parameter name="platform-name" value="linux"/>
        <parameter name="page-load-strategy" value="normal"/>
        <parameter name="user-agent" value="MyTestRunner/1.0"/>
        <parameter name="accept-insecure-certs" value="true"/>
        <parameter name="options" value="--start-maximized,--disable-infobars"/>

        <parameter name="timeout-page-load-ms" value="30000"/>
        <parameter name="timeout-script-ms" value="30000"/>
        <parameter name="timeout-implicit-ms" value="0"/>
        <parameter name="timeout-explicit-ms" value="30000"/>

        <parameter name="retry-max-attempts" value="3"/>
        <parameter name="retry-initial-delay-ms" value="100"/>
        <parameter name="retry-multiplier" value="2.0"/>

        <parameter name="screenshot" value="true"/>
        <parameter name="screenshot-path" value="build/screenshots"/>
        <parameter name="screenshot-mode" value="on-failure"/>
        <parameter name="screenshot-format" value="png"/>

        <parameter name="allure" value="false"/>
        <parameter name="report-path" value="build/allure-results"/>
        <parameter name="video-enabled" value="false"/>
    </bootstrap>
</extensions>
```

## Environment variables

```
SELENIUM_HOST                    # Selenium hub / standalone URL
SELENIUM_BROWSER                 # chrome | firefox | edge | safari
SELENIUM_BROWSER_VERSION
SELENIUM_PLATFORM
SELENIUM_USER_AGENT
SELENIUM_PAGE_LOAD_STRATEGY
SELENIUM_ACCEPT_INSECURE_CERTS   # true | false
SELENIUM_OPTIONS                 # comma-separated browser flags
SELENIUM_PROFILE                 # named profile selector

SELENIUM_TIMEOUT_PAGE_LOAD_MS
SELENIUM_TIMEOUT_SCRIPT_MS
SELENIUM_TIMEOUT_IMPLICIT_MS
SELENIUM_TIMEOUT_EXPLICIT_MS

SELENIUM_RETRY_MAX_ATTEMPTS
SELENIUM_RETRY_INITIAL_DELAY_MS
SELENIUM_RETRY_MULTIPLIER

SELENIUM_SCREENSHOT              # true | false
SELENIUM_SCREENSHOT_PATH
SELENIUM_SCREENSHOT_MODE         # off | on-failure | every-step
SELENIUM_SCREENSHOT_FORMAT       # png | webp

SELENIUM_ALLURE                  # true | false
SELENIUM_REPORT_PATH
SELENIUM_VIDEO_ENABLED
```

## Per-test overrides

Use `#[UseSelenium]` named arguments to override the resolved configuration
for a single test:

```php
#[UseSelenium(
    browser: 'firefox',
    profile: 'mobile',
    timeoutSeconds: 60,
    retryAttempts: 5,
    screenshot: true,
    capabilities: [
        'goog:chromeOptions' => ['mobileEmulation' => ['deviceName' => 'Pixel 5']],
    ],
    tags: ['critical'],
)]
public function testFoo(): void { /* ... */ }
```

The attribute is repeatable; the merge order is parents-first, then the
class itself, then the method.
