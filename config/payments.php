<?php

use App\Enums\PaymentProvider;

return [
    /*
    |--------------------------------------------------------------------------
    | Default Payment Provider
    |--------------------------------------------------------------------------
    |
    | This option controls the default payment provider that will be used
    | for processing position payments. You can change this to any of the
    | supported providers: lemon_squeezy, paddle, or creem.
    |
    */

    'default_provider' => env('PAYMENT_PROVIDER', PaymentProvider::LemonSqueezy->value),

    /*
    |--------------------------------------------------------------------------
    | Payment Provider Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for each payment provider. Add your API keys and other
    | provider-specific settings here.
    |
    */

    'providers' => [
        'lemon_squeezy' => [
            'api_key' => env('LEMON_SQUEEZY_API_KEY'),
            'store_id' => env('LEMON_SQUEEZY_STORE_ID'),
            'webhook_secret' => env('LEMON_SQUEEZY_WEBHOOK_SECRET'),
            'variants' => [
                'regular' => env('LEMON_SQUEEZY_VARIANT_REGULAR'),
                'featured' => env('LEMON_SQUEEZY_VARIANT_FEATURED'),
                'top' => env('LEMON_SQUEEZY_VARIANT_TOP'),
            ],
        ],

        'paddle' => [
            'vendor_id' => env('PADDLE_VENDOR_ID'),
            'api_key' => env('PADDLE_API_KEY'),
            'public_key' => env('PADDLE_PUBLIC_KEY'),
            'webhook_secret' => env('PADDLE_WEBHOOK_SECRET'),
        ],

        'creem' => [
            'api_key' => env('CREEM_API_KEY'),
            'merchant_id' => env('CREEM_MERCHANT_ID'),
            'webhook_secret' => env('CREEM_WEBHOOK_SECRET'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Pricing Configuration
    |--------------------------------------------------------------------------
    |
    | Pricing for each position tier. All tiers have a 30-day duration.
    |
    */

    'pricing' => [
        'regular' => 49.00,
        'featured' => 99.00,
        'top' => 199.00,
    ],

    /*
    |--------------------------------------------------------------------------
    | Duration
    |--------------------------------------------------------------------------
    |
    | Duration in days for all position tiers.
    |
    */

    'duration_days' => 30,
];
