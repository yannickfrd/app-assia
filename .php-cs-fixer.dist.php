<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__)
    ->exclude('config')
    ->exclude('node_modules')
    ->exclude('public')
    ->exclude('var')
    ->exclude('vendor')
    ->notPath('bin/console')
;

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@PSR12' => true,
        '@Symfony' => true,
        'array_syntax' => ['syntax' => 'short'],
    ])
    ->setLineEnding(PHP_EOL)
    ->setFinder($finder)
    ->setCacheFile(__DIR__.'/var/.php_cs.cache')
;
