<?php

declare(strict_types=1);

namespace Daycry\PHPUnit\Selenium\Storage;

use Facebook\WebDriver\Cookie;
use Facebook\WebDriver\Remote\RemoteWebDriver;

final readonly class CookieJar
{
    public function __construct(private RemoteWebDriver $driver)
    {
    }

    public function set(
        string $name,
        string $value,
        ?string $domain = null,
        ?string $path = '/',
        ?bool $secure = null,
        ?int $expiry = null,
    ): self {
        $cookie = new Cookie($name, $value);
        if ($domain !== null) {
            $cookie->setDomain($domain);
        }
        if ($path !== null) {
            $cookie->setPath($path);
        }
        if ($secure !== null) {
            $cookie->setSecure($secure);
        }
        if ($expiry !== null) {
            $cookie->setExpiry($expiry);
        }

        $this->driver->manage()->addCookie($cookie);

        return $this;
    }

    public function get(string $name): ?Cookie
    {
        return $this->driver->manage()->getCookieNamed($name);
    }

    public function value(string $name): ?string
    {
        return $this->get($name)?->getValue();
    }

    public function delete(string $name): self
    {
        $this->driver->manage()->deleteCookieNamed($name);

        return $this;
    }

    public function clear(): self
    {
        $this->driver->manage()->deleteAllCookies();

        return $this;
    }

    /**
     * @return list<Cookie>
     */
    public function all(): array
    {
        return $this->driver->manage()->getCookies();
    }
}
