# Browsers

The library ships with first-class support for Chrome, Firefox, Edge and
Safari. Each browser has its own capabilities builder; the right one is
selected from the resolved `browser-name`.

## Switching browsers

In `phpunit.xml`:

```xml
<parameter name="browser-name" value="firefox"/>
```

Or per CI job:

```bash
SELENIUM_BROWSER=firefox vendor/bin/phpunit
```

Or per test:

```php
#[UseSelenium(browser: 'edge')]
public function testEdgeOnly(): void { /* ... */ }
```

## Hub URLs

Each browser typically runs in its own Selenium standalone container or in
a Selenium Grid node:

```bash
docker run --rm -p 4444:4444 --shm-size=2g selenium/standalone-chrome:4
docker run --rm -p 4445:4444 --shm-size=2g selenium/standalone-firefox:4
docker run --rm -p 4446:4444 --shm-size=2g selenium/standalone-edge:4
```

Point each test at the right hub:

```bash
SELENIUM_HOST=http://localhost:4445/wd/hub SELENIUM_BROWSER=firefox vendor/bin/phpunit
```

## Browser-specific options

Use the `options` parameter for command-line flags (split by `,`):

```xml
<parameter name="options" value="--headless=new,--disable-gpu,--window-size=1920,1080"/>
```

For deeper customisation (preferences, binary paths, mobile emulation,
extensions) build the typed capability objects in your own bootstrapper:

```php
use Daycry\PHPUnit\Selenium\Browser\Capabilities\ChromeCapabilities;
```

and feed them through a custom `ConfigSource` if needed.
