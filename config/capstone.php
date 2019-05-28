<?php

return [
    'features' => [
        'redirectable-content' => env('LINTOL_FEATURE_REDIRECTABLE_CONTENT', false),
        'remote-data-resources' => env('LINTOL_FEATURE_REMOTE_DATA_RESOURCES', false),
        'services-github' => env('LINTOL_FEATURE_SERVICES_GITHUB', false),

        /**
         * This should _never_ be on on a public-facing site - it is for local testing
         * only when an OAuth provider is not available, and is passwordless.
         *
         * It should be false, or the email address of the admin user to auto-login.
         */
        'local-admin-login' => env('LINTOL_FEATURE_LOCAL_ADMIN_LOGIN', false),
    ],
    'documents' => [
        'terms-and-conditions' => env('LINTOL_DOCUMENT_PREFIX', '') . '/saas-terms-support.pdf',
        'privacy-notice' => env('LINTOL_DOCUMENT_PREFIX', '') . '/privacy-terms.pdf'
    ],
    'wamp' => [
        'realm' => env('LINTOL_CAPSTONE_REALM', 'realm1'),
        'url' => env('LINTOL_CAPSTONE_URL', 'ws://172.19.0.1:8081/ws'),
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
