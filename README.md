# laravel-mpesa-invoices

Auto-generates professional PDF invoices for M-Pesa payments and stores/emails them.

## Requirements

- PHP 8.1+
- Laravel 10 / 11 / 12 / 13
- `felixmuhoro/laravel-mpesa: ^1.2`
- `barryvdh/laravel-dompdf: ^2.0`

## Installation

```bash
composer require felixmuhoro/laravel-mpesa-invoices
```

Publish the config file:

```bash
php artisan vendor:publish --tag=mpesa-invoices-config
```

Run migrations:

```bash
php artisan migrate
```

## Configuration

Set the following in your `.env`:

```dotenv
INVOICE_BUSINESS_NAME="Acme Kenya Ltd"
INVOICE_BUSINESS_ADDRESS="P.O. Box 12345-00100, Nairobi, Kenya"
INVOICE_BUSINESS_PHONE="+254 700 123 456"
INVOICE_BUSINESS_EMAIL="billing@acme.co.ke"
INVOICE_BUSINESS_WEBSITE="https://acme.co.ke"
INVOICE_KRA_PIN="P051234567X"
INVOICE_VAT_NUMBER="V000123456Z"   # optional

INVOICE_TAX_RATE=16                # VAT percentage (Kenyan standard)
INVOICE_TAX_ENABLED=true
INVOICE_CURRENCY=KES

INVOICE_AUTO_EMAIL=false           # auto-email on PaymentSuccessful event
INVOICE_STORAGE_DISK=local
INVOICE_STORAGE_PATH=invoices
```

## Usage

### Basic — from a payment array

```php
use FelixMuhoro\MpesaInvoices\Invoice;

$invoice = Invoice::fromPayment([
    'mpesa_receipt'  => 'QHX1234567',
    'amount'         => 1500.00,
    'phone'          => '0712345678',
    'customer_name'  => 'Jane Doe',
    'customer_email' => 'jane@example.com',
    'description'    => 'Premium Subscription',
]);

// Download as PDF response
return $invoice->download();

// Get HTML string
$html = $invoice->toHtml();

// Store PDF to disk + create DB record
$record = $invoice->store();

// Send email (with PDF attachment)
$invoice->send('jane@example.com');
```

### Via Facade

```php
use FelixMuhoro\MpesaInvoices\Facades\MpesaInvoice;

$invoice = MpesaInvoice::createFromPayment($paymentArray, store: true);
$record  = MpesaInvoice::findByReceipt('QHX1234567');
$record  = MpesaInvoice::findByNumber('INV-202401-0001');
```

### With multiple line items

```php
$invoice = Invoice::fromPayment([
    'mpesa_receipt' => 'ZZZ9876543',
    'phone'         => '0722222222',
    'customer_name' => 'John Kamau',
    'items' => [
        ['description' => 'Product A', 'quantity' => 2, 'unit_price' => 500, 'amount' => 1000],
        ['description' => 'Delivery',  'quantity' => 1, 'unit_price' => 200, 'amount' =>  200],
    ],
]);
```

### Auto-generate on payment events

Register the listener in `EventServiceProvider`:

```php
use FelixMuhoro\MpesaInvoices\Listeners\GenerateInvoiceOnPayment;

protected $listen = [
    \YourApp\Events\PaymentSuccessful::class => [
        GenerateInvoiceOnPayment::class,
    ],
];
```

Your event must expose either:
- a `$payment` property (array/object), or
- a `toPaymentArray()` method, or
- be itself castable to the expected keys.

### HasInvoices trait

Add the trait to any Eloquent model (e.g. `Order`, `User`):

```php
use FelixMuhoro\MpesaInvoices\Concerns\HasInvoices;

class Order extends Model
{
    use HasInvoices;
}

// Usage:
$order->invoices();       // MorphMany
$order->latestInvoice();  // ?InvoiceRecord
```

When storing, link to the model:

```php
$invoice->store($order);
```

### HTTP Routes

Two routes are registered automatically (protected by `auth` middleware):

| Method | URL | Action |
|--------|-----|--------|
| GET    | `/invoices/{invoiceNumber}/download` | Stream PDF download |
| POST   | `/invoices/{invoiceNumber}/email`    | Re-send by email |

POST body for email: `{ "email": "override@example.com" }` (optional).

## Invoice Number Format

```
INV-202401-0001
^^^  ^^^^^^  ^^^^
|    |        |-- 4-digit sequence (resets per month)
|    |--- YYYYMM
|--- prefix (configurable)
```

## KRA / VAT Compliance

The PDF template includes:
- Business KRA PIN
- VAT Registration Number (when set)
- Tax line item breakdown (VAT 16%)
- Official KRA-compliant disclaimer

## Publishing Views

To customise the invoice template:

```bash
php artisan vendor:publish --tag=mpesa-invoices-views
```

The Blade template will be copied to `resources/views/vendor/mpesa-invoices/`.

## Testing

```bash
composer test
```

## License

MIT — Felix Muhoro <hi@felixmuhoro.dev>
