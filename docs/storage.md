# Cookies and Storage

The `Browser` facade exposes three storage adapters with consistent fluent
APIs: cookies, `window.localStorage` and `window.sessionStorage`.

## Cookies

```php
use Daycry\PHPUnit\Selenium\Locator\Locator;

$this->browser()->cookies()
    ->set('session', 'abc123', domain: '.example.test', secure: true)
    ->set('locale', 'es');

$value = $this->browser()->cookies()->value('session');     // string|null
$cookie = $this->browser()->cookies()->get('session');      // Cookie|null
$all = $this->browser()->cookies()->all();                  // list<Cookie>

$this->browser()->cookies()->delete('locale');
$this->browser()->cookies()->clear();
```

Most browsers refuse to set cookies before any page is loaded; visit the
target domain first.

## Local storage

```php
$this->browser()
    ->visit('/login')
    ->localStorage()->set('feature.flag.beta', '1');

$value = $this->browser()->localStorage()->get('feature.flag.beta');

$this->browser()->localStorage()->remove('feature.flag.beta');
$this->browser()->localStorage()->clear();
```

## Session storage

Same shape as `localStorage()`, scoped to the current tab:

```php
$this->browser()->sessionStorage()
    ->set('cart.draft', json_encode(['items' => 2]))
    ->get('cart.draft');
```

## Asserting state

`SeleniumAssertions` covers the cookie case directly:

```php
$this->assertCookie('session');
$this->assertCookie('locale', 'es');
```

For storage values, combine the helper with a PHPUnit assertion:

```php
self::assertSame('1', $this->browser()->localStorage()->get('feature.flag.beta'));
```

## Cleanup between tests

Every test gets its own `RemoteWebDriver` (via `SeleniumSession`), so
storage and cookies are not shared across tests by default. If you want
extra paranoia (for example, you visit the same domain you stored cookies
for), call `clear()` in your test's `setUp()` after the visit.
