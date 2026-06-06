<?php

declare(strict_types=1);

namespace FelixMuhoro\MpesaInvoices\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \FelixMuhoro\MpesaInvoices\Invoice createFromPayment(object|array $paymentEvent, bool $store = false)
 * @method static \FelixMuhoro\MpesaInvoices\Models\InvoiceRecord|null findByReceipt(string $receipt)
 * @method static \FelixMuhoro\MpesaInvoices\Models\InvoiceRecord|null findByNumber(string $number)
 *
 * @see \FelixMuhoro\MpesaInvoices\InvoiceManager
 */
class MpesaInvoice extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'mpesa-invoices';
    }
}
