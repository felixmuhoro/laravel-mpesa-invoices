<?php

declare(strict_types=1);

namespace FelixMuhoro\MpesaInvoices;

use Carbon\Carbon;

final readonly class InvoiceData
{
    /**
     * @param  InvoiceItem[]  $items
     */
    public function __construct(
        public string  $invoice_number,
        public Carbon  $issued_at,
        public string  $customer_name,
        public string  $customer_phone,
        public ?string $customer_email,
        public array   $items,
        public float   $subtotal,
        public float   $tax,
        public float   $total,
        public string  $currency,
        public string  $mpesa_receipt,

        // Business details (pulled from config at creation time)
        public string  $business_name,
        public string  $business_address,
        public string  $business_phone,
        public string  $business_email,
        public ?string $business_website,
        public string  $kra_pin,
        public ?string $vat_number,
        public ?string $logo_path,
        public string  $tax_label,
    ) {}

    /**
     * Create an InvoiceData from a raw attribute array (e.g. stored as JSON).
     */
    public static function fromArray(array $data): self
    {
        $items = array_map(
            fn (array $item) => InvoiceItem::fromArray($item),
            $data['items'] ?? []
        );

        return new self(
            invoice_number:   $data['invoice_number'],
            issued_at:        Carbon::parse($data['issued_at']),
            customer_name:    $data['customer_name'],
            customer_phone:   $data['customer_phone'],
            customer_email:   $data['customer_email'] ?? null,
            items:            $items,
            subtotal:         (float) $data['subtotal'],
            tax:              (float) $data['tax'],
            total:            (float) $data['total'],
            currency:         $data['currency'],
            mpesa_receipt:    $data['mpesa_receipt'],
            business_name:    $data['business_name'],
            business_address: $data['business_address'],
            business_phone:   $data['business_phone'],
            business_email:   $data['business_email'],
            business_website: $data['business_website'] ?? null,
            kra_pin:          $data['kra_pin'],
            vat_number:       $data['vat_number'] ?? null,
            logo_path:        $data['logo_path'] ?? null,
            tax_label:        $data['tax_label'] ?? 'VAT (16%)',
        );
    }

    public function toArray(): array
    {
        return [
            'invoice_number'   => $this->invoice_number,
            'issued_at'        => $this->issued_at->toIso8601String(),
            'customer_name'    => $this->customer_name,
            'customer_phone'   => $this->customer_phone,
            'customer_email'   => $this->customer_email,
            'items'            => array_map(fn (InvoiceItem $i) => $i->toArray(), $this->items),
            'subtotal'         => $this->subtotal,
            'tax'              => $this->tax,
            'total'            => $this->total,
            'currency'         => $this->currency,
            'mpesa_receipt'    => $this->mpesa_receipt,
            'business_name'    => $this->business_name,
            'business_address' => $this->business_address,
            'business_phone'   => $this->business_phone,
            'business_email'   => $this->business_email,
            'business_website' => $this->business_website,
            'kra_pin'          => $this->kra_pin,
            'vat_number'       => $this->vat_number,
            'logo_path'        => $this->logo_path,
            'tax_label'        => $this->tax_label,
        ];
    }
}
