<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;
use Rector\TypeDeclaration\Rector\ClassMethod\ParamTypeByMethodCallTypeRector;
use RectorLaravel\Set\LaravelSetList;

return RectorConfig::configure()
    ->withPaths([
        __DIR__.'/app',
        __DIR__.'/tests',
    ])
    ->withSets([
        LevelSetList::UP_TO_PHP_84,
        SetList::DEAD_CODE,
        SetList::EARLY_RETURN,
        SetList::TYPE_DECLARATION,
        LaravelSetList::LARAVEL_130,
    ])
    ->withSkip([
        __DIR__.'/app/Http/Middleware/RedirectIfAuthenticated.php',
        ParamTypeByMethodCallTypeRector::class => [
            __DIR__.'/app/Filament/Resources/Expenses/Tables/ExpenseTable.php',
        ],
    ]);
