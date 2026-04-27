<?php

return [
    'pdfme' => [
        'node_binary' => env('PDFME_NODE_BINARY', 'node'),
        'generator_script' => resource_path('js/pdfme-generate-certificate.mjs'),
        'fonts' => [
            'CormorantGaramond' => [
                'path' => public_path('fonts/certificates/CormorantGaramond-Regular.ttf'),
                'public_path' => 'fonts/certificates/CormorantGaramond-Regular.ttf',
                'fallback' => true,
                'subset' => true,
            ],
            'GreatVibes' => [
                'path' => public_path('fonts/certificates/GreatVibes-Regular.ttf'),
                'public_path' => 'fonts/certificates/GreatVibes-Regular.ttf',
                'fallback' => false,
                'subset' => true,
            ],
        ],
    ],
];
