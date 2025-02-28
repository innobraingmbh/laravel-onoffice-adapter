<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\Class_\InlineConstructorDefaultToPropertyRector;
use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;
use RectorLaravel\Rector\Expr\AppEnvironmentComparisonToParameterRector;
use RectorLaravel\Rector\FuncCall\RemoveDumpDataDeadCodeRector;

return RectorConfig::configure()
    ->withPaths([
        __DIR__.'/config',
        __DIR__.'/resources',
        __DIR__.'/src',
        __DIR__.'/tests',
    ])
    ->withRules([
        InlineConstructorDefaultToPropertyRector::class,
        RemoveDumpDataDeadCodeRector::class,
        AppEnvironmentComparisonToParameterRector::class,
    ])
    ->withSets([
        LevelSetList::UP_TO_PHP_82,
        SetList::DEAD_CODE,
        SetList::CODE_QUALITY,
        SetList::EARLY_RETURN,
        RectorLaravel\Set\LaravelLevelSetList::UP_TO_LARAVEL_110,
        RectorLaravel\Set\LaravelSetList::LARAVEL_CODE_QUALITY,
        RectorLaravel\Set\LaravelSetList::LARAVEL_IF_HELPERS,
        RectorLaravel\Set\LaravelSetList::LARAVEL_FACADE_ALIASES_TO_FULL_NAMES,
        RectorLaravel\Set\LaravelSetList::LARAVEL_COLLECTION,
        RectorLaravel\Set\LaravelSetList::LARAVEL_ARRAY_STR_FUNCTION_TO_STATIC_CALL,
        RectorLaravel\Set\LaravelSetList::LARAVEL_ARRAYACCESS_TO_METHOD_CALL,
        RectorLaravel\Set\LaravelSetList::LARAVEL_CONTAINER_STRING_TO_FULLY_QUALIFIED_NAME,
        RectorLaravel\Set\LaravelSetList::LARAVEL_ELOQUENT_MAGIC_METHOD_TO_QUERY_BUILDER,
    ]);
