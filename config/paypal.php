<?php

return [

    /*
    |--------------------------------------------------------------------------
    | PayPal REST credentials
    |--------------------------------------------------------------------------
    |
    | Created in https://developer.paypal.com (Apps & Credentials). Use the
    | sandbox app while testing and the live app in production. Online payment
    | stays hidden in the portal until both values are present.
    |
    */

    'client_id' => env('PAYPAL_CLIENT_ID'),
    'secret' => env('PAYPAL_SECRET'),

    // sandbox | live
    'mode' => env('PAYPAL_MODE', 'sandbox'),

    // ID of the webhook created in the PayPal app, used to verify signatures.
    'webhook_id' => env('PAYPAL_WEBHOOK_ID'),

    'currency' => env('PAYPAL_CURRENCY', 'EUR'),

    // Name shown on the PayPal approval page.
    'brand_name' => env('PAYPAL_BRAND_NAME', env('APP_NAME', 'Laravel')),

];
