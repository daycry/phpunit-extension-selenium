<?php

declare(strict_types=1);

namespace Daycry\PHPUnit\Selenium\Tests\Unit\Browser;

use Daycry\PHPUnit\Selenium\Browser\Key;
use Facebook\WebDriver\WebDriverKeys;
use PHPUnit\Framework\TestCase;

final class KeyTest extends TestCase
{
    public function testCommonKeysMapToWebDriverKeys(): void
    {
        self::assertSame(WebDriverKeys::ENTER, Key::Enter->value);
        self::assertSame(WebDriverKeys::TAB, Key::Tab->value);
        self::assertSame(WebDriverKeys::ESCAPE, Key::Escape->value);
    }
}
