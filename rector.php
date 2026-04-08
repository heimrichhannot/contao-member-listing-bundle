<?php

declare(strict_types=1);

use Contao\Rector\Set\ContaoLevelSetList;
use Contao\Rector\Set\ContaoSetList;
use HeimrichHannot\MemberListingBundle\Member\Member;
use Rector\Config\RectorConfig;
use Rector\Php74\Rector\Property\RestoreDefaultNullToNullableTypePropertyRector;
use Rector\Php81\Rector\Array_\ArrayToFirstClassCallableRector;
use Rector\Php84\Rector\Param\ExplicitNullableParamTypeRector;
use Rector\Set\ValueObject\LevelSetList;
use Rector\TypeDeclaration\Rector\ClassMethod\AddVoidReturnTypeWhereNoReturnRector;
use Rector\ValueObject\PhpVersion;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
        __DIR__ . '/contao',

    ])
    ->withPhpVersion(PhpVersion::PHP_84)
    ->withRules([
        AddVoidReturnTypeWhereNoReturnRector::class,
        ExplicitNullableParamTypeRector::class,
    ])

    ->withImportNames(
        importShortClasses: false,
        removeUnusedImports: true
    )
    ->withComposerBased(
        twig: true,
        doctrine: true,
        phpunit: true,
        symfony: true,
    )
    ->withSets([
        LevelSetList::UP_TO_PHP_82,
        ContaoLevelSetList::UP_TO_CONTAO_53,
        ContaoSetList::FQCN,
        ContaoSetList::ANNOTATIONS_TO_ATTRIBUTES,
    ])
    ->withSkip([
        ArrayToFirstClassCallableRector::class,
        RestoreDefaultNullToNullableTypePropertyRector::class => [
            __DIR__ . '/src/Member/Member.php'
        ]
    ])
;
