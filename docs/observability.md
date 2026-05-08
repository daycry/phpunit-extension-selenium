# Observability

Three layers of observability are built into the extension:

1. **PSR-3 logging** at every lifecycle event.
2. **Screenshots** with metadata-rich filenames.
3. **Allure reporting** (optional) plus browser console log capture.

## PSR-3 logging

`SeleniumExtension` resolves a `Psr\Log\LoggerInterface` from the service
container; by default it is a `NullLogger`. Replace it by extending the
`ContainerBuilder` or providing your own service container in a custom
extension subclass.

Events emitted:

| Channel/level  | Event id                              | When                                          |
|----------------|---------------------------------------|-----------------------------------------------|
| `info`         | `selenium.bootstrap`                  | Extension wired, before any test runs         |
| `info`         | `selenium.session.started`            | Driver up for a test                          |
| `error`        | `selenium.session.start_failed`       | Driver creation failed                        |
| `info`         | `selenium.session.closed`             | Driver quit after a test                      |
| `notice`       | `selenium.shutdown.cleanup`           | Sessions left dangling at runner shutdown     |
| `warning`      | `selenium.test.failed`                | A test failed (after screenshot capture)      |
| `warning`      | `selenium.test.failed.screenshot_error` | Screenshot capture itself failed            |
| `debug`        | `selenium.action.start`               | Before every wrapped action                   |
| `debug`        | `selenium.action.ok`                  | Action succeeded                              |
| `warning`      | `selenium.action.fail`                | Action threw                                  |

The structured context includes `test_id`, `browser`, `session_id`,
`duration_ms` where applicable.

To replace the logger, build a custom container:

```php
use Daycry\PHPUnit\Selenium\Container\ContainerBuilder;
use Daycry\PHPUnit\Selenium\Container\ServiceContainer;
use Daycry\PHPUnit\Selenium\Config\Loader\EnvConfigSource;
use Daycry\PHPUnit\Selenium\Config\Loader\XmlConfigSource;

$container = (new ContainerBuilder())->build([
    new XmlConfigSource($parameters),
    new EnvConfigSource(),
]);

$container->instance(\Psr\Log\LoggerInterface::class, $myMonologLogger);
```

Then use the same wiring `SeleniumExtension::bootstrap()` does, in your
own subclass, to register the subscribers with the customised container.

## Screenshots

Captured by `Subscriber\FailedTestSubscriber` through `ScreenshotService`
and `FilenameSanitizer`. Filename pattern:

```
{ISO8601}_{class}_{method}_{browser}_{status}_{shortSession}.{ext}
```

Example:

```
20260508T103045Z_App-Tests-LoginTest_testHappyPath_chrome_failed_a1b2c3d4.png
```

| Knob                          | XML / env                                | Default                               |
|-------------------------------|------------------------------------------|---------------------------------------|
| Enable                        | `screenshot` / `SELENIUM_SCREENSHOT`     | `false`                               |
| Output directory              | `screenshot-path` / `SELENIUM_SCREENSHOT_PATH` | `sys_get_temp_dir()/selenium-screenshots` |
| Mode                          | `screenshot-mode` / `SELENIUM_SCREENSHOT_MODE` | `on-failure`                       |
| Format                        | `screenshot-format` / `SELENIUM_SCREENSHOT_FORMAT` | `png`                            |

`screenshot-mode` accepts `off`, `on-failure`, `every-step`. `every-step`
is implemented as a decorator wrapped around `BrowserActions`; every
state-mutating call captures a frame so debugging stays easy at the cost
of disk space.

`screenshot-format=webp` requires the GD extension with WebP support.

## Allure

The `allure-framework/allure-phpunit` package is declared as `suggest`. To
enable it:

```bash
composer require --dev allure-framework/allure-phpunit
```

```xml
<parameter name="allure" value="true"/>
<parameter name="report-path" value="build/allure-results"/>
```

When the optional package is installed and `allure=true`,
`AllureReporter` becomes active. On a failure the
`FailedTestSubscriber` calls:

1. `attachFile('screenshot', $screenshotPath, 'image/png')`
2. `attachText('browser-console', json_encode($logs), 'application/json')`
   gathered through `BrowserLogCollector`.

If the package is not installed, the reporter degrades to a no-op — your
tests keep running unchanged.

## Browser console logs

`BrowserLogCollector::collect()` returns:

```php
[
    'browser' => [
        ['level' => 'SEVERE', 'message' => '…', 'timestamp' => 1715167845000],
        // …
    ],
    'driver' => [/* same shape */],
]
```

Browser support varies: Chrome / Edge expose the `browser` log type by
default, Firefox needs `goog:loggingPrefs` capabilities, Safari does not
support log retrieval at all. The collector is best-effort; missing
support yields an empty list.

## Video recording (optional)

A flag is reserved in `ReportingConfig` to enable video recording when
running on a Selenium Grid 4 setup with the `selenium/video` sidecar.

```xml
<parameter name="video-enabled" value="true"/>
```

Setting the flag emits the `se:recordVideo=true` capability. Producing
the actual MP4 is the responsibility of the Grid topology — the library
does not move artefacts around. See the integration workflow in
`.github/workflows/integration.yml` for a reference setup.
