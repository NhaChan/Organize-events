<?php

return [
    /*
    | Search engine indexing
    |
    | Keep this disabled on local/staging environments. An administrator can
    | override the value from the website settings page.
    */
    'indexing_enabled' => env('SEO_INDEXING_ENABLED', true),

    'robots' => [
        'allow' => [
            '/',
            '/*.js$',
            '/*.css$',
            '/images/',
            '/storage/',
        ],
        'disallow' => [
            '/admin/',
            '/login',
            '/*?q=',
            '/*&q=',
            '/*?category=',
            '/*&category=',
        ],
    ],
];
