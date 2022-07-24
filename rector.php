<?php

declare(strict_types=1);

use Rector\CodingStyle\Rector\Stmt\NewlineAfterStatementRector;
use Rector\Config\RectorConfig;
use Rector\Core\ValueObject\PhpVersion;
use Rector\Doctrine\Set\DoctrineSetList;
use Rector\Nette\Set\NetteSetList;
use Rector\Php80\Rector\Class_\AnnotationToAttributeRector;
use Rector\Php80\Rector\Class_\DoctrineAnnotationClassToAttributeRector;
use Rector\Php80\ValueObject\AnnotationToAttribute;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;
use Rector\Symfony\Set\SensiolabsSetList;
use Rector\Symfony\Set\SymfonySetList;
use Rector\Transform\Rector\Attribute\AttributeKeyToClassConstFetchRector;
use Rector\Transform\ValueObject\AttributeKeyToClassConstFetch;

// vendor/bin/rector process src --dry-run --xdebug --vvv

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__.'/src',
        __DIR__.'/tests',
    ]);

    $rectorConfig->phpVersion(PhpVersion::PHP_81);

    $rectorConfig->phpstanConfig(__DIR__.'/phpstan.neon');

    $rectorConfig->disableParallel();

    $rectorConfig->sets([
        LevelSetList::UP_TO_PHP_80,
        LevelSetList::UP_TO_PHP_81,
        // SetList::CODE_QUALITY,
        // SetList::CODING_STYLE,
        DoctrineSetList::ANNOTATIONS_TO_ATTRIBUTES,
        SymfonySetList::ANNOTATIONS_TO_ATTRIBUTES,
        NetteSetList::ANNOTATIONS_TO_ATTRIBUTES,
        SensiolabsSetList::FRAMEWORK_EXTRA_61,
    ]);

    $rectorConfig->ruleWithConfiguration(
        DoctrineAnnotationClassToAttributeRector::class, [
        DoctrineAnnotationClassToAttributeRector::REMOVE_ANNOTATIONS => true,
    ]);

    $rectorConfig->ruleWithConfiguration(
        AnnotationToAttributeRector::class,
        [new AnnotationToAttribute('Symfony\Component\Routing\Annotation\Route')]
    );

    $rectorConfig->ruleWithConfiguration(
        AttributeKeyToClassConstFetchRector::class, [
            new AttributeKeyToClassConstFetch('Doctrine\ORM\Mapping\Column', 'type', 'Doctrine\DBAL\Types\Types', [
                    'datetime' => 'DATETIME_MUTABLE',
                    'boolean' => 'BOOLEAN',
                    'float' => 'FLOAT',
                    'integer' => 'INTEGER',
                    'json' => 'JSON',
                    'smallint' => 'SMALLINT',
                    'string' => 'STRING',
                    'text' => 'TEXT',
                ]),
            ]
    );

    $rectorConfig->rules([
        NewlineAfterStatementRector::class,
    ]);
};
