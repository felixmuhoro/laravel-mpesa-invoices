<?php

declare(strict_types=1);

namespace FelixMuhoro\MpesaInvoices;

use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use FelixMuhoro\MpesaInvoices\Models\InvoiceRecord;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class Invoice
{
    public function __construct(
        protected InvoiceData $data,
    ) {}

    // ------------------------------------------------------------------ //
    //  Factory
    // ------------------------------------------------------------------ //

    /**
     * Build an Invoice instance from a raw payment payload array.
     *
     * Expected keys: mpesa_receipt, amount, phone, customer_name,
     *                customer_email (optional), description (optional),
     *                items (optional array of item arrays).
     */
    public static function fromPayment(array $payment): self
    {
        $config   = config('mpesa-invoices');
        $business = $config['business'];
        $taxCfg   = $config['tax'];
        $currency = $config['currency'] ?? 'KES';

        // Build items list
        if (!empty($payment['items'])) {
            $items = array_map(fn ($i) => InvoiceItem::fromArray($i), $payment['items']);
        } else {
            $amount    = (float) ($payment['amount'] ?? 0);
            $desc      = $payment['description'] ?? 'M-Pesa Payment';
            $items     = [new InvoiceItem($desc, 1, $amount, $amount)];
        }

        // Calculate totals
        $subtotal = array_sum(array_map(fn (InvoiceItem $i) => $i->amount, $items));
        $taxRate  = $taxCfg['enabled'] ? ((float) $taxCfg['rate'] / 100) : 0;
        $tax      = round($subtotal * $taxRate, 2);
        $total    = round($subtotal + $tax, 2);

        $invoiceNumber = static::generateInvoiceNumber();

        $data = new InvoiceData(
            invoice_number:   $invoiceNumber,
            issued_at:        Carbon::now(),
            customer_name:    $payment['customer_name'] ?? 'Valued Customer',
            customer_phone:   $payment['phone'] ?? '',
            customer_email:   $payment['customer_email'] ?? null,
            items:            $items,
            subtotal:         $subtotal,
            tax:              $tax,
            total:            $total,
            currency:         $currency,
            mpesa_receipt:    $payment['mpesa_receipt'] ?? '',
            business_name:    $business['name'],
            business_address: $business['address'],
            business_phone:   $business['phone'],
            business_email:   $business['email'],
            business_website: $business['website'] ?? null,
            kra_pin:          $business['kra_pin'],
            vat_number:       $business['vat_number'] ?? null,
            logo_path:        $business['logo_path'] ?? null,
            tax_label:        $taxCfg['label'] ?? 'VAT (16%)',
        );

        return new self($data);
    }

    // ------------------------------------------------------------------ //
    //  Rendering
    // ------------------------------------------------------------------ //

    public function toHtml(): string
    {
        return view('mpesa-invoices::invoice', ['invoice' => $this->data])->render();
    }

    public function toPdf(): \Barryvdh\DomPDF\PDF
    {
        $cfg = config('mpesa-invoices.pdf', []);

        return Pdf::loadView('mpesa-invoices::invoice', ['invoice' => $this->data])
            ->setPaper($cfg['paper'] ?? 'a4', $cfg['orientation'] ?? 'portrait')
            ->setOptions($cfg['options'] ?? []);
    }

    public function download(string $filename = null): Response
    {
        $filename ??= $this->data->invoice_number . '.pdf';

        return $this->toPdf()->download($filename);
    }

    // ------------------------------------------------------------------ //
    //  Storage
    // ------------------------------------------------------------------ //

    /**
     * Persist the PDF to disk and save a database record.
     * Returns the stored InvoiceRecord.
     */
    public function store(object $invoiceable = null): InvoiceRecord
    {
        $storageCfg = config('mpesa-invoices.storage');
        $disk       = $storageCfg['disk']   ?? 'local';
        $prefix     = $storageCfg['prefix'] ?? 'invoices';

        $filename = $this->data->invoice_number . '.pdf';
        $path     = $prefix . '/' . $filename;

        Storage::disk($disk)->put($path, $this->toPdf()->output());

        $record = new InvoiceRecord();
        $record->invoice_number = $this->data->invoice_number;
        $record->mpesa_receipt  = $this->data->mpesa_receipt;
        $record->amount         = $this->data->total;
        $record->customer_phone = $this->data->customer_phone;
        $record->customer_name  = $this->data->customer_name;
        $record->customer_email = $this->data->customer_email;
        $record->pdf_path       = $path;
        $record->payload        = $this->data->toArray();

        if ($invoiceable !== null) {
            $record->invoiceable()->associate($invoiceable);
        }

        $record->save();

        return $record;
    }

    // ------------------------------------------------------------------ //
    //  Email
    // ------------------------------------------------------------------ //

    /**
     * Send the invoice PDF by email to the given address (or the customer's email).
     */
    public function send(string $email = null): self
    {
        $to      = $email ?? $this->data->customer_email;
        $subject = str_replace(
            ':business',
            $this->data->business_name,
            config('mpesa-invoices.email.subject', 'Your Invoice from :business')
        );

        $pdfContent  = $this->toPdf()->output();
        $filename    = $this->data->invoice_number . '.pdf';
        $invoiceData = $this->data;

        Mail::send(
            'mpesa-invoices::email',
            compact('invoiceData'),
            function ($message) use ($to, $subject, $pdfContent, $filename) {
                $fromCfg = config('mpesa-invoices.email.from', []);
                $message
                    ->to($to)
                    ->subject($subject)
                    ->from($fromCfg['address'] ?? config('mail.from.address'), $fromCfg['name'] ?? config('mail.from.name'))
                    ->attachData($pdfContent, $filename, ['mime' => 'application/pdf']);
            }
        );

        return $this;
    }

    // ------------------------------------------------------------------ //
    //  Accessors
    // ------------------------------------------------------------------ //

    public function getData(): InvoiceData
    {
        return $this->data;
    }

    // ------------------------------------------------------------------ //
    //  Helpers
    // ------------------------------------------------------------------ //

    protected static function generateInvoiceNumber(): string
    {
        $prefix    = config('mpesa-invoices.numbering.prefix', 'INV');
        $padLen    = (int) config('mpesa-invoices.numbering.sequence_length', 4);
        $yearMonth = now()->format('Ym');

        // Atomic sequence: count existing invoices this month and increment
        $count = InvoiceRecord::whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->count() + 1;

        return sprintf('%s-%s-%s', $prefix, $yearMonth, str_pad((string) $count, $padLen, '0', STR_PAD_LEFT));
    }
}
