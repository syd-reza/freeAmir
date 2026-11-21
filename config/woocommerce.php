<?php

return [
    /*
    |--------------------------------------------------------------------------
    | WordPress / WooCommerce store
    |--------------------------------------------------------------------------
    | The public Store API (wp-json/wc/store) is used to read product prices
    | and stock without needing API keys. Optional consumer credentials unlock
    | the authenticated wc/v3 endpoints when they become available.
    */
    'base_url' => rtrim((string) env('WOOCOMMERCE_URL', 'https://motolavazem.ir'), '/'),
    'consumer_key' => env('WOOCOMMERCE_CONSUMER_KEY'),
    'consumer_secret' => env('WOOCOMMERCE_CONSUMER_SECRET'),
    'per_page' => (int) env('WOOCOMMERCE_PER_PAGE', 100),
    'ca_bundle' => env('WOOCOMMERCE_CA_BUNDLE', storage_path('app/cacert.pem')),
    'create_missing_products' => (bool) env('WOOCOMMERCE_CREATE_PRODUCTS', true),
    'default_group' => env('WOOCOMMERCE_DEFAULT_GROUP', 'کالاهای فروشگاه'),
    'sync_vat_rate' => (float) env('WOOCOMMERCE_PRODUCT_VAT', 10),

    /*
    |--------------------------------------------------------------------------
    | Partnership profit split
    |--------------------------------------------------------------------------
    */
    'team_share_percent' => (float) env('PARTNERSHIP_TEAM_PERCENT', 50),
    'settlement_vat_percent' => (float) env('PARTNERSHIP_SETTLEMENT_VAT', 10),
];
