<?php

namespace RandomItemsBlock;

return [
    'block_layouts' => [
        'invokables' => [
            'randomItems' => Site\BlockLayout\RandomItems::class,
        ],
    ],
    'view_helpers' => [
        'factories' => [
            'randomItems' => Service\ViewHelper\RandomItemsFactory::class,
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            dirname(__DIR__) . '/view',
        ],
    ],
];
