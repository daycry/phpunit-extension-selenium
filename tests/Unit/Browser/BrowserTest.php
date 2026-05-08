<?php

declare(strict_types=1);

namespace Daycry\PHPUnit\Selenium\Tests\Unit\Browser;

use Daycry\PHPUnit\Selenium\Browser\BrowserType;
use Daycry\PHPUnit\Selenium\Exception\ConfigurationException;
use PHPUnit\Framework\TestCase;

final class BrowserTest extends TestCase
{
    /**
     * @return iterable<string, array{string, BrowserType}>
     */
    public static function nameProvider(): iterable
    {
        yield 'chrome lowercase' => ['chrome', BrowserType::Chrome];
        yield 'chrome upper' => ['CHROME', BrowserType::Chrome];
        yield 'chrome trimmed' => ['  chrome  ', BrowserType::Chrome];
        yield 'firefox' => ['firefox', BrowserType::Firefox];
        yield 'edge' => ['edge', BrowserType::Edge];
        yield 'safari' => ['safari', BrowserType::Safari];
    }

    /**
     * @dataProvider nameProvider
     */
    public function testFromNameResolvesKnownBrowsers(string $input, BrowserType $expected): void
    {
        self::assertSame($expected, BrowserType::fromName($input));
    }

    public function testFromNameRejectsUnknownBrowser(): void
    {
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage('Unsupported browser "ie"');

        BrowserType::fromName('ie');
    }
}
