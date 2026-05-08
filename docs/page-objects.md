# Page Objects

Page Objects encapsulate per-screen knowledge so tests can speak in domain
terms (`loginAs`, `submitOrder`) instead of repeating selectors.

The library ships two base classes:

- `Daycry\PHPUnit\Selenium\Page\Page` — a page (a thing with a `url()`).
- `Daycry\PHPUnit\Selenium\Page\Component` — a reusable UI fragment scoped
  by a root locator (header, modal, table row…).

`Page` already pulls in `SeleniumAssertions` and provides a `browser()`
method that returns the constructor-injected facade, so subclasses can
write `$this->assertVisible(...)` and `$this->browser->click(...)` directly.

## A minimal page

```php
use Daycry\PHPUnit\Selenium\Browser\Browser;
use Daycry\PHPUnit\Selenium\Locator\Locator;
use Daycry\PHPUnit\Selenium\Page\Page;

final class LoginPage extends Page
{
    public readonly Locator $email;
    public readonly Locator $password;
    public readonly Locator $submit;

    public function __construct(Browser $browser)
    {
        parent::__construct($browser);
        $this->email = Locator::name('email');
        $this->password = Locator::name('password');
        $this->submit = Locator::testId('login-submit');
    }

    public function url(): string
    {
        return '/login';
    }

    public function loginAs(string $user, string $password): DashboardPage
    {
        $this->browser
            ->type($this->email, $user)
            ->type($this->password, $password)
            ->click($this->submit);

        return new DashboardPage($this->browser);
    }
}
```

## Using it from a test

```php
use Daycry\PHPUnit\Selenium\Attributes\UseSelenium;
use Daycry\PHPUnit\Selenium\Session\SeleniumAware;
use PHPUnit\Framework\TestCase;

final class LoginTest extends TestCase
{
    use SeleniumAware;

    #[UseSelenium]
    public function testLogin(): void
    {
        $login = new LoginPage($this->browser());

        $login->visit()->loginAs('a@b.io', 'secret');

        $this->browser()->wait()->forUrl('/dashboard')->run();
    }
}
```

## Asserting from inside a Page Object

```php
final class DashboardPage extends Page
{
    public function url(): string
    {
        return '/dashboard';
    }

    public function assertGreetsUser(string $name): self
    {
        $this->assertOnPage();
        $this->assertTextContains(Locator::testId('greeting'), $name);

        return $this;
    }
}
```

`assertOnPage()` defaults to `assertUrlContains($this->url())`. Override it
when a stronger invariant is needed (a stable element, a title fragment).

## Components

For repeatable UI fragments (header, modal, table row), extend `Component`:

```php
use Daycry\PHPUnit\Selenium\Browser\Browser;
use Daycry\PHPUnit\Selenium\Locator\Locator;
use Daycry\PHPUnit\Selenium\Page\Component;

final class Header extends Component
{
    public function logout(): void
    {
        $this->browser->click(Locator::testId('logout'));
    }
}
```

A component receives both the `Browser` and a `root` locator at
construction time, so multiple instances on the same page don't collide.

```php
$header = new Header(
    browser: $this->browser(),
    root: Locator::css('header[data-section="main"]'),
);
$header->logout();
```

You can scope your selectors to the component root with relative locators
(`Locator::xpath('.//button')`, `Locator::css('& > .item')`, …).

## Anti-patterns to avoid

- **Mixing assertions and navigation in the same method**: a Page Object
  method should either *do* something (returning the next page) or *check*
  something (returning `self`), not both. Tests stay readable when each
  method has one job.
- **Returning the underlying `RemoteWebDriver`**: the whole point of the
  Page Object is to hide implementation details behind domain methods.
  Reach for `$this->browser->driver()` only when you genuinely need
  WebDriver-specific calls that the facade does not expose.
