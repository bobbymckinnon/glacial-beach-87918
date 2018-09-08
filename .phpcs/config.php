<?php

$finder = PhpCsFixer\Finder::create();
$config = PhpCsFixer\Config::create()->setFinder($finder);

$finder
    ->in(__DIR__ . '/..')
    ->exclude('app/')
    ->exclude('web/')
    ->exclude('var/')
    ->exclude('bin/')
;

$rules = [
    '@Symfony' => true,
    '@Symfony:risky' => true,
    'array_syntax' => ['syntax' => 'short'],
    'concat_space' => ['spacing' => 'one'],
    'combine_consecutive_unsets' => true,
    'binary_operator_spaces' => ['align_double_arrow' => false, 'align_equals' => false],
    'declare_strict_types' => true,
    'general_phpdoc_annotation_remove' => [
        'expectedException',
        'expectedExceptionMessage',
        'expectedExceptionMessageRegExp'
    ],
    'heredoc_to_nowdoc' => true,
    'no_extra_consecutive_blank_lines' => [
        'break',
        'continue',
        'extra',
        'return',
        'throw',
        'use',
        'parenthesis_brace_block',
        'square_brace_block',
        'curly_brace_block'
    ],
    'no_short_echo_tag' => true,
    'no_unreachable_default_argument_value' => true,
    'no_useless_else' => true,
    'no_useless_return' => true,
    'ordered_class_elements' => true,
    'ordered_imports' => true,
    'php_unit_strict' => false,
    'phpdoc_add_missing_param_annotation' => true,
    'phpdoc_order' => true,
    'semicolon_after_instruction' => true,
    'strict_comparison' => true,
    'strict_param' => true,
];

$config
    ->setRiskyAllowed(true)
    ->setRules($rules)
;

return $config;
