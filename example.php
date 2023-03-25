<?php

use WyriHaximus\SubSplitTools\Files;

require 'vendor/autoload.php';

Files::setUp(
    __DIR__ . '/templates',
    __DIR__ . '/example',
    [
        'packageName' => 'github',
        'fullName' => 'GitHub',
        'namespace' => 'GitHub',
        'specUrl' => 'https://raw.githubusercontent.com/github/rest-api-description/main/descriptions-next/api.github.com/api.github.com.yaml',
        'branch' => 'v0.2.x',
        'requires' => [
            [
                'name' => 'github-common',
                'version' => '^0.3',
            ],
        ],
        'suggests' => [
            [
                'name' => 'github-common',
                'reason' => 'Common Resources',
            ],
            [
                'name' => 'github-pulls',
                'reason' => 'Pull Request Related Resources',
            ],
        ],
    ],
);
