<?php

declare(strict_types=1);

namespace FelixMuhoro\MpesaInvoices\Concerns;

use FelixMuhoro\MpesaInvoices\Models\InvoiceRecord;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasInvoices
{
    /**
     * All invoice records attached to this model.
     */
    public function invoices(): MorphMany
    {
        return $this->morphMany(InvoiceRecord::class, 'invoiceable')->latest();
    }

    /**
     * The most recent invoice record for this model.
     */
    public function latestInvoice(): ?InvoiceRecord
    {
        return $this->invoices()->first();
    }

    /**
     * Retrieve an invoice by its unique invoice number.
     */
    public function invoiceByNumber(string $number): ?InvoiceRecord
    {
        return $this->invoices()->where('invoice_number', $number)->first();
    }
}
