<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Core\ValueObject\PhpVersion;
use Rector\Php80\Rector\Class_\AnnotationToAttributeRector;
use Rector\Php80\ValueObject\AnnotationToAttribute;
use Rector\Set\ValueObject\LevelSetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__.'/src',
        __DIR__.'/tests',
    ]);

    $rectorConfig->phpVersion(PhpVersion::PHP_81);

    $rectorConfig->phpstanConfig(__DIR__.'/phpstan.neon');

    // // register a single rule
    // $rectorConfig->rules([
    //     InlineConstructorDefaultToPropertyRector::class,
    // ]);

    $rectorConfig->ruleWithConfiguration(
        AnnotationToAttributeRector::class,
        [new AnnotationToAttribute('Symfony\Component\Routing\Annotation\Route')]
    );

    // define sets of rules
    $rectorConfig->sets([
        LevelSetList::UP_TO_PHP_80,
        LevelSetList::UP_TO_PHP_81,
    ]);
};
