<?php

declare(strict_types=1);

namespace Daycry\PHPUnit\Selenium\Tests\Unit\Screenshot;

use DateTimeImmutable;
use DateTimeZone;
use Daycry\PHPUnit\Selenium\Screenshot\FilenameSanitizer;
use PHPUnit\Framework\TestCase;

final class FilenameSanitizerTest extends TestCase
{
    public function testBuildIncludesAllParts(): void
    {
        $sanitizer = new FilenameSanitizer();
        $now = new DateTimeImmutable('2026-05-08T10:30:45', new DateTimeZone('UTC'));

        $name = $sanitizer->build(
            className: 'Tests\\Foo\\BarTest',
            methodName: 'testHappyPath',
            browser: 'chrome',
            status: 'failed',
            sessionId: 'abc-123-def-456',
            extension: 'png',
            now: $now,
        );

        self::assertStringContainsString('20260508T103045Z', $name);
        self::assertStringContainsString('Tests-Foo-BarTest', $name);
        self::assertStringContainsString('testHappyPath', $name);
        self::assertStringContainsString('chrome', $name);
        self::assertStringContainsString('failed', $name);
        self::assertStringContainsString('abc123de', $name);
        self::assertStringEndsWith('.png', $name);
    }

    public function testWithoutSessionIdOmitsThatPart(): void
    {
        $sanitizer = new FilenameSanitizer();
        $now = new DateTimeImmutable('2026-05-08T10:30:45', new DateTimeZone('UTC'));

        $name = $sanitizer->build(
            className: 'A',
            methodName: 'b',
            browser: 'firefox',
            status: 'failed',
            extension: 'png',
            now: $now,
        );

        self::assertSame('20260508T103045Z_A_b_firefox_failed.png', $name);
    }

    public function testSanitiseReplacesUnsafeCharacters(): void
    {
        $sanitizer = new FilenameSanitizer();

        self::assertSame('a-b-c', $sanitizer->sanitize('a/b\\c'));
        self::assertSame('a-b-c', $sanitizer->sanitize('a::b::c'));
        self::assertSame('safe.name', $sanitizer->sanitize('safe.name'));
        self::assertSame('mixed-name', $sanitizer->sanitize('mixed name!'));
    }
}
