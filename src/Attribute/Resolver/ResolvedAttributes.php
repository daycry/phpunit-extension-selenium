<?php

declare(strict_types=1);

namespace Daycry\PHPUnit\Selenium\Attribute\Resolver;

use Daycry\PHPUnit\Selenium\Attributes\UseSelenium;

/**
 * Aggregated view of all `#[UseSelenium]` attributes resolved for a test method,
 * after walking the class hierarchy and merging method-level overrides on top.
 *
 * The {@see effective} attribute reflects the final values that should be applied;
 * the {@see chain} preserves declaration order (parents first, method last) for
 * inspection/reporting.
 */
final readonly class ResolvedAttributes
{
    /**
     * @param list<UseSelenium> $chain Ordered list of resolved attributes from the closest base class to the method.
     */
    public function __construct(
        public UseSelenium $effective,
        public array $chain,
    ) {
    }

    /**
     * Merge a chain of attributes from base-class to method-level. Later entries
     * override earlier ones field-by-field whenever they are non-null.
     *
     * @param list<UseSelenium> $chain
     */
    public static function merge(array $chain): self
    {
        if ($chain === []) {
            return new self(new UseSelenium(), []);
        }

        $merged = new UseSelenium();

        foreach ($chain as $attribute) {
            $merged = new UseSelenium(
                browser: $attribute->browser ?? $merged->browser,
                profile: $attribute->profile ?? $merged->profile,
                timeoutSeconds: $attribute->timeoutSeconds ?? $merged->timeoutSeconds,
                pageLoadTimeoutMs: $attribute->pageLoadTimeoutMs ?? $merged->pageLoadTimeoutMs,
                retryAttempts: $attribute->retryAttempts ?? $merged->retryAttempts,
                screenshot: $attribute->screenshot ?? $merged->screenshot,
                capabilities: [...$merged->capabilities, ...$attribute->capabilities],
                browserVersion: $attribute->browserVersion ?? $merged->browserVersion,
                platform: $attribute->platform ?? $merged->platform,
                tags: array_values(array_unique([...$merged->tags, ...$attribute->tags])),
            );
        }

        return new self($merged, $chain);
    }
}
