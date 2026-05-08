# Filling Forms

Form interactions are first-class on the `Browser` facade. Three categories
of helpers cover the common cases:

- Field-by-field: `type`, `clear`, `fill`, `check`, `uncheck`, `select`,
  `upload`, `pressKey`.
- One-shot: `fillForm()` accepts a list of `[Locator, value]` tuples and
  dispatches to the right helper based on the value's type.
- Tagged value objects (`Select`, `Upload`, `Date`) so `fillForm()` can
  pick the right method without sniffing strings.

## Direct helpers

```php
use Daycry\PHPUnit\Selenium\Browser\Key;
use Daycry\PHPUnit\Selenium\Locator\Locator;

$this->browser()
    ->type(Locator::name('email'), 'a@b.io')
    ->fill(Locator::name('search'), 'phpunit')          // clear + type
    ->check(Locator::name('terms'))
    ->uncheck(Locator::name('marketing'))
    ->select(Locator::id('country'), Select::byLabel('Spain'))
    ->upload(Locator::name('avatar'), '/tmp/face.png')
    ->pressKey(Key::Enter);
```

`type()` accepts an optional `delayMs` argument that introduces a per-key
sleep; useful for inputs with key-by-key debouncing or autocomplete.

```php
$this->browser()->type(Locator::id('quick-search'), 'react', delayMs: 25);
```

## `Select` value object

```php
use Daycry\PHPUnit\Selenium\Form\Select;

Select::byValue('ES');     // matches <option value="ES">
Select::byLabel('Spain');  // matches the visible text
Select::byIndex(2);        // matches the 3rd option (0-based)
```

## `Upload` value object

```php
use Daycry\PHPUnit\Selenium\Form\Upload;

Upload::file('/tmp/face.png');   // throws if the file does not exist
```

## `Date` value object

`Date` formats a date with the format you choose, then sends it as text. It
is a convenience around `type()` for date pickers that accept ISO input;
for native pickers driven by JavaScript, target the visible input by name
and use `fill()` with the formatted string directly.

```php
use Daycry\PHPUnit\Selenium\Form\Date as FormDate;

FormDate::of('1990-01-01');                  // → "1990-01-01"
FormDate::of('1990-01-01', 'd/m/Y');         // → "01/01/1990"
FormDate::fromDateTime(new \DateTimeImmutable('2026-05-08'));
```

## `fillForm()` one-shot

Pass a list of `[Locator, value]` pairs. The dispatcher picks the right
helper:

| Value type                  | Effective call                    |
|-----------------------------|-----------------------------------|
| `string`                    | `fill($locator, $value)`          |
| `bool`                      | `check` / `uncheck`               |
| `Select`                    | `select($locator, $value)`        |
| `Upload`                    | `upload($locator, $value->path)`  |
| `Date`                      | `fill($locator, $value->asString())` |

```php
use Daycry\PHPUnit\Selenium\Form\Date as FormDate;
use Daycry\PHPUnit\Selenium\Form\Select;
use Daycry\PHPUnit\Selenium\Form\Upload;
use Daycry\PHPUnit\Selenium\Locator\Locator;

$this->browser()->fillForm([
    [Locator::name('email'),     'a@b.io'],
    [Locator::name('password'),  'secret'],
    [Locator::name('terms'),     true],
    [Locator::name('country'),   Select::byLabel('Spain')],
    [Locator::name('birthday'),  FormDate::of('1990-01-01')],
    [Locator::name('avatar'),    Upload::file('/tmp/face.png')],
]);

$this->browser()->click(Locator::testId('signup-submit'));
```

## File uploads through Selenium Grid

When the test runner and the browser run in different containers, you must
use the file detector so the local file is uploaded to the grid host:

```php
use Facebook\WebDriver\Remote\LocalFileDetector;

$this->browser()->driver()->setFileDetector(new LocalFileDetector());
$this->browser()->upload(Locator::name('avatar'), '/local/path/to/file.png');
```

`upload()` validates the file exists locally before sending it; otherwise
the test fails fast with a `SeleniumException`.
