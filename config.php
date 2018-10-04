<?php
return [
    'log_file' => 'log.txt',
    'commission' => [
        'cash_in' => [
            'natural' => [ // private person
                'amount' => 0.03, // % - commission rate
                'max' => 5 // in EUR
            ],
            'legal' => [ // business entity
                'amount' => 0.03, // % - commission rate
                'max' => 5 // in EUR
            ],
        ],
        'cash_out' => [
            'natural' => [ // private person
                'amount' => 0.3, // % - commission rate
                'min' => 0 // in EUR - minimum charge
            ],
            'legal' => [ // business entity
                'amount' => 0.3, // % - commission rate
                'min' => 0.5 // in EUR - minimum charge
            ],
        ],
    ],
    'discounts' => [
        'free_weekly_limit' => [
            'max_transactions' => 3,
            'max_amount' => 1000,
        ]
    ],
    'currency' => [
        'exchange_rates' => [
            'default_cur' => 'EUR',
            'EUR' => [
                'USD' => 1.1497,
                'JPY' => 129.53,
            ]
        ],
        'precision' => [ // the decimal points for the different currencies
            'EUR' => 2,
            'USD' => 2,
            'JPY' => 0,
        ]
    ],
];
