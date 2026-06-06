<?php

declare(strict_types=1);

namespace FelixMuhoro\MpesaInvoices\Models;

use FelixMuhoro\MpesaInvoices\InvoiceData;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Storage;

class InvoiceRecord extends Model
{
    protected $fillable = [
        'invoice_number',
        'mpesa_receipt',
        'amount',
        'customer_phone',
        'customer_name',
        'customer_email',
        'pdf_path',
        'emailed_at',
        'payload',
        'invoiceable_type',
        'invoiceable_id',
    ];

    protected $casts = [
        'amount'     => 'float',
        'emailed_at' => 'datetime',
        'payload'    => 'array',
    ];

    public function getTable(): string
    {
        return config('mpesa-invoices.table', 'invoice_records');
    }

    // ------------------------------------------------------------------ //
    //  Relationships
    // ------------------------------------------------------------------ //

    public function invoiceable(): MorphTo
    {
        return $this->morphTo();
    }

    // ------------------------------------------------------------------ //
    //  Helpers
    // ------------------------------------------------------------------ //

    /**
     * Reconstruct the InvoiceData DTO from the stored payload.
     */
    public function getInvoiceData(): ?InvoiceData
    {
        if (empty($this->payload)) {
            return null;
        }

        return InvoiceData::fromArray($this->payload);
    }

    /**
     * Get the public URL (or local path) for the stored PDF.
     */
    public function getPdfUrl(): ?string
    {
        if (!$this->pdf_path) {
            return null;
        }

        $disk = config('mpesa-invoices.storage.disk', 'local');

        return Storage::disk($disk)->url($this->pdf_path);
    }

    /**
     * Retrieve raw PDF content from storage.
     */
    public function getPdfContent(): ?string
    {
        if (!$this->pdf_path) {
            return null;
        }

        $disk = config('mpesa-invoices.storage.disk', 'local');

        return Storage::disk($disk)->get($this->pdf_path);
    }

    /**
     * Mark the invoice as emailed and persist.
     */
    public function markEmailed(): self
    {
        $this->emailed_at = now();
        $this->save();

        return $this;
    }
}
