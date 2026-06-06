<?php

declare(strict_types=1);

namespace FelixMuhoro\MpesaInvoices;

use FelixMuhoro\MpesaInvoices\Models\InvoiceRecord;

class InvoiceManager
{
    /**
     * Create an Invoice (and optionally persist it) from a payment event payload.
     *
     * The $paymentEvent can be any object or array with the following fields:
     *   - mpesa_receipt / MpesaReceiptNumber
     *   - amount / Amount / TransAmount
     *   - phone / PhoneNumber / MSISDN
     *   - customer_name (optional)
     *   - customer_email (optional)
     *   - description (optional)
     *
     * Pass $store = true to also persist the record and PDF to disk.
     */
    public function createFromPayment(object|array $paymentEvent, bool $store = false): Invoice
    {
        $payload = $this->normalise($paymentEvent);
        $invoice = Invoice::fromPayment($payload);

        if ($store) {
            $invoice->store();
        }

        return $invoice;
    }

    /**
     * Find a stored invoice record by M-Pesa receipt number.
     */
    public function findByReceipt(string $receipt): ?InvoiceRecord
    {
        return InvoiceRecord::where('mpesa_receipt', $receipt)->first();
    }

    /**
     * Find a stored invoice record by invoice number (e.g. INV-202401-0001).
     */
    public function findByNumber(string $number): ?InvoiceRecord
    {
        return InvoiceRecord::where('invoice_number', $number)->first();
    }

    // ------------------------------------------------------------------ //
    //  Internal helpers
    // ------------------------------------------------------------------ //

    /**
     * Normalise a mixed event (object or array, various key conventions) into
     * the standard array expected by Invoice::fromPayment().
     */
    protected function normalise(object|array $event): array
    {
        $arr = is_array($event) ? $event : (array) $event;

        return [
            'mpesa_receipt'  => $arr['mpesa_receipt']
                ?? $arr['MpesaReceiptNumber']
                ?? $arr['CheckoutRequestID']
                ?? '',
            'amount'         => $arr['amount']
                ?? $arr['Amount']
                ?? $arr['TransAmount']
                ?? 0,
            'phone'          => $arr['phone']
                ?? $arr['PhoneNumber']
                ?? $arr['MSISDN']
                ?? '',
            'customer_name'  => $arr['customer_name']  ?? $arr['FirstName'] ?? 'Valued Customer',
            'customer_email' => $arr['customer_email'] ?? $arr['email']      ?? null,
            'description'    => $arr['description']    ?? $arr['AccountReference'] ?? 'M-Pesa Payment',
            'items'          => $arr['items']          ?? [],
        ];
    }
}
