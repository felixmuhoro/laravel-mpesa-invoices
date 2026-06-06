<?php

declare(strict_types=1);

namespace FelixMuhoro\MpesaInvoices\Listeners;

use FelixMuhoro\MpesaInvoices\Invoice;
use FelixMuhoro\MpesaInvoices\Models\InvoiceRecord;
use Illuminate\Support\Facades\Log;

class GenerateInvoiceOnPayment
{
    /**
     * Handle the PaymentSuccessful event (or any compatible event object).
     *
     * The event must expose one of the following:
     *   - a public $payment array/object property
     *   - a toPaymentArray() method
     *   - be itself array-castable with the expected keys
     */
    public function handle(object $event): void
    {
        try {
            $payload = $this->extractPayload($event);
            $invoice = Invoice::fromPayment($payload);
            $record  = $invoice->store();

            if (config('mpesa-invoices.auto_email') && !empty($payload['customer_email'])) {
                $invoice->send($payload['customer_email']);
                $record->markEmailed();
            }
        } catch (\Throwable $e) {
            Log::error('[MpesaInvoices] Failed to generate invoice on payment', [
                'event' => get_class($event),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    protected function extractPayload(object $event): array
    {
        if (method_exists($event, 'toPaymentArray')) {
            return $event->toPaymentArray();
        }

        if (property_exists($event, 'payment')) {
            $p = $event->payment;
            return is_array($p) ? $p : (array) $p;
        }

        return (array) $event;
    }
}
