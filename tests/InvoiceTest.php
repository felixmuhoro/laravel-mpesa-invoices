<?php

declare(strict_types=1);

namespace FelixMuhoro\MpesaInvoices\Tests;

use FelixMuhoro\MpesaInvoices\Invoice;
use FelixMuhoro\MpesaInvoices\InvoiceData;
use FelixMuhoro\MpesaInvoices\InvoiceItem;
use FelixMuhoro\MpesaInvoices\InvoiceManager;
use FelixMuhoro\MpesaInvoices\MpesaInvoicesServiceProvider;
use Illuminate\Support\Facades\Config;
use Orchestra\Testbench\TestCase;

class InvoiceTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [MpesaInvoicesServiceProvider::class];
    }

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('mpesa-invoices.business', [
            'name'       => 'Acme Kenya Ltd',
            'address'    => 'P.O. Box 12345, Nairobi',
            'phone'      => '+254 700 111 222',
            'email'      => 'billing@acme.co.ke',
            'website'    => 'https://acme.co.ke',
            'kra_pin'    => 'P051234567X',
            'vat_number' => 'V000123456Z',
            'logo_path'  => null,
        ]);

        Config::set('mpesa-invoices.tax', [
            'rate'    => 16,
            'label'   => 'VAT (16%)',
            'enabled' => true,
        ]);

        Config::set('mpesa-invoices.currency', 'KES');
        Config::set('mpesa-invoices.numbering', ['prefix' => 'INV', 'sequence_length' => 4]);
    }

    // ------------------------------------------------------------------ //
    //  InvoiceItem
    // ------------------------------------------------------------------ //

    public function test_invoice_item_from_array(): void
    {
        $item = InvoiceItem::fromArray([
            'description' => 'Premium Plan',
            'quantity'    => 2,
            'unit_price'  => 500.00,
            'amount'      => 1000.00,
        ]);

        $this->assertSame('Premium Plan', $item->description);
        $this->assertSame(2, $item->quantity);
        $this->assertSame(500.00, $item->unit_price);
        $this->assertSame(1000.00, $item->amount);
    }

    public function test_invoice_item_infers_amount(): void
    {
        $item = InvoiceItem::fromArray([
            'description' => 'Service',
            'quantity'    => 3,
            'unit_price'  => 200.00,
        ]);

        $this->assertSame(600.00, $item->amount);
    }

    public function test_invoice_item_to_array_roundtrip(): void
    {
        $data = [
            'description' => 'Widget',
            'quantity'    => 1,
            'unit_price'  => 999.00,
            'amount'      => 999.00,
        ];

        $item = InvoiceItem::fromArray($data);

        $this->assertSame($data, $item->toArray());
    }

    // ------------------------------------------------------------------ //
    //  Invoice::fromPayment
    // ------------------------------------------------------------------ //

    public function test_invoice_from_payment_calculates_tax(): void
    {
        $invoice = Invoice::fromPayment([
            'mpesa_receipt' => 'QHX2345678',
            'amount'        => 1000.00,
            'phone'         => '0712345678',
            'customer_name' => 'Jane Doe',
        ]);

        $data = $invoice->getData();

        $this->assertSame(1000.00, $data->subtotal);
        $this->assertSame(160.00, $data->tax);
        $this->assertSame(1160.00, $data->total);
        $this->assertSame('KES', $data->currency);
        $this->assertSame('QHX2345678', $data->mpesa_receipt);
        $this->assertSame('Jane Doe', $data->customer_name);
        $this->assertStringStartsWith('INV-', $data->invoice_number);
    }

    public function test_invoice_from_payment_with_items(): void
    {
        $invoice = Invoice::fromPayment([
            'mpesa_receipt' => 'RYZ9876543',
            'phone'         => '0722222222',
            'customer_name' => 'John Kamau',
            'items' => [
                ['description' => 'Item A', 'quantity' => 2, 'unit_price' => 500, 'amount' => 1000],
                ['description' => 'Item B', 'quantity' => 1, 'unit_price' => 250, 'amount' => 250],
            ],
        ]);

        $data = $invoice->getData();

        $this->assertCount(2, $data->items);
        $this->assertSame(1250.00, $data->subtotal);
        $this->assertSame(200.00, $data->tax);
        $this->assertSame(1450.00, $data->total);
    }

    public function test_invoice_number_format(): void
    {
        $invoice = Invoice::fromPayment([
            'mpesa_receipt' => 'ABC1234567',
            'amount'        => 500,
            'phone'         => '0711111111',
        ]);

        $number = $invoice->getData()->invoice_number;

        // Should match INV-YYYYMM-XXXX
        $this->assertMatchesRegularExpression('/^INV-\d{6}-\d{4}$/', $number);
    }

    public function test_tax_disabled(): void
    {
        Config::set('mpesa-invoices.tax.enabled', false);

        $invoice = Invoice::fromPayment([
            'mpesa_receipt' => 'ZZZ0000001',
            'amount'        => 800.00,
            'phone'         => '0733333333',
        ]);

        $data = $invoice->getData();

        $this->assertSame(0.00, $data->tax);
        $this->assertSame($data->subtotal, $data->total);
    }

    // ------------------------------------------------------------------ //
    //  InvoiceData::toArray / fromArray roundtrip
    // ------------------------------------------------------------------ //

    public function test_invoice_data_serialisation_roundtrip(): void
    {
        $original = Invoice::fromPayment([
            'mpesa_receipt'  => 'SER123456',
            'amount'         => 2000.00,
            'phone'          => '0744444444',
            'customer_name'  => 'Alice Wanjiku',
            'customer_email' => 'alice@example.com',
        ]);

        $array       = $original->getData()->toArray();
        $restored    = InvoiceData::fromArray($array);

        $this->assertSame($original->getData()->invoice_number, $restored->invoice_number);
        $this->assertSame($original->getData()->total, $restored->total);
        $this->assertSame($original->getData()->mpesa_receipt, $restored->mpesa_receipt);
        $this->assertSame($original->getData()->customer_email, $restored->customer_email);
    }

    // ------------------------------------------------------------------ //
    //  HTML rendering
    // ------------------------------------------------------------------ //

    public function test_to_html_contains_invoice_number(): void
    {
        $invoice = Invoice::fromPayment([
            'mpesa_receipt' => 'HTML123456',
            'amount'        => 300.00,
            'phone'         => '0755555555',
            'customer_name' => 'Bob Otieno',
        ]);

        $html = $invoice->toHtml();

        $this->assertStringContainsString($invoice->getData()->invoice_number, $html);
        $this->assertStringContainsString('HTML123456', $html);
        $this->assertStringContainsString('Bob Otieno', $html);
        $this->assertStringContainsString('M-PESA', $html);
        $this->assertStringContainsString('KRA PIN', $html);
    }

    // ------------------------------------------------------------------ //
    //  InvoiceManager
    // ------------------------------------------------------------------ //

    public function test_manager_create_from_payment(): void
    {
        $manager = new InvoiceManager();

        $invoice = $manager->createFromPayment([
            'mpesa_receipt' => 'MGR1234567',
            'amount'        => 450.00,
            'phone'         => '0766666666',
            'customer_name' => 'Carol Mwangi',
        ]);

        $this->assertInstanceOf(Invoice::class, $invoice);
        $this->assertSame('MGR1234567', $invoice->getData()->mpesa_receipt);
    }

    public function test_manager_normalises_safaricom_keys(): void
    {
        $manager = new InvoiceManager();

        $invoice = $manager->createFromPayment([
            'MpesaReceiptNumber' => 'SAF9876543',
            'TransAmount'        => 750.00,
            'MSISDN'             => '0777777777',
            'FirstName'          => 'David Kamau',
        ]);

        $data = $invoice->getData();

        $this->assertSame('SAF9876543', $data->mpesa_receipt);
        $this->assertSame(750.00, $data->subtotal);
        $this->assertSame('David Kamau', $data->customer_name);
    }
}
