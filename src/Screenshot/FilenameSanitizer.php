<?php

declare(strict_types=1);

namespace Daycry\PHPUnit\Selenium\Screenshot;

use DateTimeImmutable;
use DateTimeZone;

final class FilenameSanitizer
{
    /**
     * Build a deterministic, filesystem-safe screenshot filename including
     * an ISO 8601 UTC timestamp, the test class+method, the browser and
     * the test status.
     */
    public function build(
        string $className,
        string $methodName,
        string $browser,
        string $status,
        ?string $sessionId = null,
        string $extension = 'png',
        ?DateTimeImmutable $now = null,
    ): string {
        $now ??= new DateTimeImmutable('now', new DateTimeZone('UTC'));
        $timestamp = $now->format('Ymd\THis\Z');

        $shortSessionId = $sessionId !== null ? substr(preg_replace('/[^A-Za-z0-9]/', '', $sessionId) ?? '', 0, 8) : null;

        $parts = array_filter([
            $timestamp,
            $this->sanitize($className),
            $this->sanitize($methodName),
            $this->sanitize($browser),
            $this->sanitize($status),
            $shortSessionId !== null && $shortSessionId !== '' ? $shortSessionId : null,
        ], static fn (?string $v): bool => $v !== null && $v !== '');

        return implode('_', $parts) . '.' . ltrim($extension, '.');
    }

    public function sanitize(string $value): string
    {
        $value = str_replace(['\\', '/', '::'], '-', $value);
        $value = preg_replace('/[^A-Za-z0-9\-_.]/', '-', $value) ?? '';
        $value = preg_replace('/-+/', '-', $value) ?? '';

        return trim($value, '-');
    }
}
