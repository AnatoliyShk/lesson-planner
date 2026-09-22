<?php

return [
    'Crustum/Mcp' => [
        'copy-safe' => [
            'config' => [
                [
                    'destination' => 'config/mcp.php',
                    'completed' => true,
                    'source' => 'vendor/crustum/mcp/config/mcp.php',
                    'installed_at' => '2026-09-22 01:33:10',
                ],
            ],
        ],
        'append' => [
            'bootstrap' => [
                [
                    'destination' => 'config/bootstrap.php',
                    'completed' => true,
                    'marker' => '// Mcp Plugin Configuration',
                    'installed_at' => '2026-09-22 01:33:10',
                ],
            ],
        ],
    ],
    '_star_prompts' => [
        'Crustum/Mcp' => [
            'asked_at' => '2026-09-22 01:33:16',
        ],
    ],
];
