<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\Class_\InlineConstructorDefaultToPropertyRector;
use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\ClassMethod\RemoveEmptyClassMethodRector;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    ->withSets([
        LevelSetList::UP_TO_PHP_82,
        SetList::CODE_QUALITY,
        SetList::DEAD_CODE,
        SetList::TYPE_DECLARATION,
        SetList::EARLY_RETURN,
    ])
    ->withRules([
        InlineConstructorDefaultToPropertyRector::class,
    ])
    ->withSkip([
        // Test fixtures intentionally hold attribute-only methods.
        RemoveEmptyClassMethodRector::class => [
            __DIR__ . '/tests',
        ],
    ])
    ->withImportNames(removeUnusedImports: true);
