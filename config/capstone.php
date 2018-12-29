<?php

return [
    'features' => [
        'redirectable-content' => env('LINTOL_FEATURE_REDIRECTABLE_CONTENT', false),
        'services-github' => env('LINTOL_FEATURE_SERVICES_GITHUB', false),
    ],
    'documents' => [
        'terms-and-conditions' => env('LINTOL_DOCUMENT_PREFIX', '') . '/saas-terms-support.pdf',
        'privacy-notice' => env('LINTOL_DOCUMENT_PREFIX', '') . '/privacy-terms.pdf'
    ],
    'wamp' => [
        'realm' => env('LINTOL_CAPSTONE_REALM', 'realm1'),
        'url' => env('LINTOL_CAPSTONE_URL', 'ws://172.19.0.1:8080/ws'),
    ],
    'frontend' => [
        'url' => env('LINTOL_FRONTEND_URL', '/'),
        'proxy' => env('LINTOL_FRONTEND_PROXY', false),
        'max-pagination' => 250
    ],
    'encryption' => [
        'blind-index-key' => env('LINTOL_BLIND_INDEX_KEY', config('app.key'))
    ],
    'authentication' => [
        'ckan' => [
            'valid-servers' => env('LINTOL_CKAN_SERVERS', false) ? explode(';', env('LINTOL_CKAN_SERVERS')) : []
        ]
    ]
];
