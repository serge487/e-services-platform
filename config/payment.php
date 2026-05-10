<?php

return [
    'whish' => [
        'number' => env('PAYMENT_WHISH_NUMBER', '+961 XX XXX XXX'),
    ],

    'crypto' => [
        'usdt_trc20' => env('PAYMENT_CRYPTO_USDT_TRC20'),
        'btc'        => env('PAYMENT_CRYPTO_BTC'),
    ],
];