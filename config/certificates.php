<?php

return [
    'pdfme' => [
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
