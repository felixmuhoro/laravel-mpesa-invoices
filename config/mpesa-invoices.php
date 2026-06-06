<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Business Details
    |--------------------------------------------------------------------------
    |
    | These details appear on every generated invoice. Fill in your business
    | information to ensure compliance with Kenyan tax regulations.
    |
    */

    'business' => [
        'name'       => env('INVOICE_BUSINESS_NAME', 'Your Business Name'),
        'address'    => env('INVOICE_BUSINESS_ADDRESS', 'P.O. Box 00100, Nairobi, Kenya'),
        'phone'      => env('INVOICE_BUSINESS_PHONE', '+254 700 000 000'),
        'email'      => env('INVOICE_BUSINESS_EMAIL', 'billing@yourbusiness.co.ke'),
        'website'    => env('INVOICE_BUSINESS_WEBSITE', 'https://yourbusiness.co.ke'),
        'kra_pin'    => env('INVOICE_KRA_PIN', 'P000000000X'),
        'vat_number' => env('INVOICE_VAT_NUMBER', null),
        'logo_path'  => env('INVOICE_LOGO_PATH', null),
    ],

    /*
    |--------------------------------------------------------------------------
    | Invoice Numbering
    |--------------------------------------------------------------------------
    |
    | Format: {prefix}-{YYYYMM}-{sequence padded to sequence_length digits}
    | Example: INV-202401-0001
    |
    */

    'numbering' => [
        'prefix'          => env('INVOICE_PREFIX', 'INV'),
        'sequence_length' => 4,
    ],

    /*
    |--------------------------------------------------------------------------
    | Tax / VAT
    |--------------------------------------------------------------------------
    |
    | Kenyan standard VAT rate is 16%. Set enabled to false if not VAT-registered.
    |
    */

    'tax' => [
        'rate'    => env('INVOICE_TAX_RATE', 16),
        'label'   => env('INVOICE_TAX_LABEL', 'VAT (16%)'),
        'enabled' => env('INVOICE_TAX_ENABLED', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Currency
    |--------------------------------------------------------------------------
    */

    'currency' => env('INVOICE_CURRENCY', 'KES'),

    /*
    |--------------------------------------------------------------------------
    | Storage
    |--------------------------------------------------------------------------
    */

    'storage' => [
        'disk'   => env('INVOICE_STORAGE_DISK', 'local'),
        'prefix' => env('INVOICE_STORAGE_PATH', 'invoices'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Email
    |--------------------------------------------------------------------------
    */

    'email' => [
        'subject' => env('INVOICE_EMAIL_SUBJECT', 'Your Invoice from :business'),
        'from'    => [
            'address' => env('MAIL_FROM_ADDRESS', 'billing@yourbusiness.co.ke'),
            'name'    => env('MAIL_FROM_NAME', 'Billing'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | PDF Options
    |--------------------------------------------------------------------------
    */

    'pdf' => [
        'paper'       => 'a4',
        'orientation' => 'portrait',
        'options'     => [
            'defaultFont'          => 'sans-serif',
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled'      => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Auto-send on Payment
    |--------------------------------------------------------------------------
    |
    | When true, GenerateInvoiceOnPayment will email the invoice automatically.
    |
    */

    'auto_email' => env('INVOICE_AUTO_EMAIL', false),

    /*
    |--------------------------------------------------------------------------
    | Database Table
    |--------------------------------------------------------------------------
    */

    'table' => 'invoice_records',

];
