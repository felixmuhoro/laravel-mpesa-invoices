<?php

declare(strict_types=1);

namespace FelixMuhoro\MpesaInvoices\Http\Controllers;

use FelixMuhoro\MpesaInvoices\Invoice;
use FelixMuhoro\MpesaInvoices\InvoiceManager;
use FelixMuhoro\MpesaInvoices\Models\InvoiceRecord;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InvoiceController extends Controller
{
    public function __construct(protected InvoiceManager $manager) {}

    /**
     * Stream the stored PDF to the browser as a download.
     *
     * GET /invoices/{invoiceNumber}/download
     */
    public function download(Request $request, string $invoiceNumber): Response|StreamedResponse
    {
        $record = $this->manager->findByNumber($invoiceNumber);

        abort_if($record === null, 404, 'Invoice not found.');

        $content = $record->getPdfContent();

        abort_if($content === null, 404, 'Invoice PDF not found on disk.');

        return response($content, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $invoiceNumber . '.pdf"',
            'Content-Length'      => strlen($content),
        ]);
    }

    /**
     * Re-send an invoice by email.
     *
     * POST /invoices/{invoiceNumber}/email
     * Body: { "email": "override@example.com" }  (optional)
     */
    public function email(Request $request, string $invoiceNumber): JsonResponse
    {
        $record = $this->manager->findByNumber($invoiceNumber);

        abort_if($record === null, 404, 'Invoice not found.');

        $invoiceData = $record->getInvoiceData();

        abort_if($invoiceData === null, 422, 'Invoice payload missing — cannot reconstruct invoice.');

        $emailTo = $request->input('email') ?? $record->customer_email ?? $invoiceData->customer_email;

        abort_if(empty($emailTo), 422, 'No email address available for this invoice.');

        $invoice = new Invoice($invoiceData);
        $invoice->send($emailTo);
        $record->markEmailed();

        return response()->json([
            'message' => 'Invoice sent successfully.',
            'invoice' => $invoiceNumber,
            'sent_to' => $emailTo,
        ]);
    }
}
