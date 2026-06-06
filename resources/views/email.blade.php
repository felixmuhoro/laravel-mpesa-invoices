<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your Invoice {{ $invoiceData->invoice_number }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 14px; color: #1a1a2e; background: #f4f4f4; margin: 0; padding: 0; }
        .wrapper { max-width: 580px; margin: 32px auto; background: #fff; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 12px rgba(0,0,0,0.08); }
        .header { background: #006437; padding: 32px 36px; text-align: center; }
        .header h1 { color: #fff; font-size: 24px; margin: 0; letter-spacing: -0.5px; }
        .header p { color: rgba(255,255,255,0.75); font-size: 13px; margin-top: 4px; }
        .body { padding: 32px 36px; }
        .body p { line-height: 1.7; color: #374151; margin-bottom: 12px; }
        .receipt-box { background: #f0faf4; border: 1px solid #006437; border-radius: 8px; padding: 16px 20px; margin: 24px 0; }
        .receipt-box .row { display: flex; justify-content: space-between; margin-bottom: 6px; }
        .receipt-box .label { color: #6b7280; font-size: 12px; }
        .receipt-box .value { font-weight: 700; color: #1a1a2e; }
        .receipt-box .total-row .value { color: #006437; font-size: 16px; }
        .receipt-code { font-size: 18px; font-weight: 800; color: #006437; letter-spacing: 2px; }
        .cta { text-align: center; margin: 28px 0; }
        .footer { background: #f9fafb; padding: 20px 36px; text-align: center; font-size: 11px; color: #9ca3af; border-top: 1px solid #e5e7eb; }
        .footer a { color: #006437; text-decoration: none; }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="header">
        <h1>{{ $invoiceData->business_name }}</h1>
        <p>Invoice {{ $invoiceData->invoice_number }}</p>
    </div>
    <div class="body">
        <p>Dear <strong>{{ $invoiceData->customer_name }}</strong>,</p>
        <p>
            Thank you for your payment. Please find your invoice attached to this email as a PDF document.
        </p>

        <div class="receipt-box">
            <div class="row">
                <span class="label">Invoice Number</span>
                <span class="value">{{ $invoiceData->invoice_number }}</span>
            </div>
            <div class="row">
                <span class="label">M-Pesa Receipt</span>
                <span class="value receipt-code">{{ $invoiceData->mpesa_receipt }}</span>
            </div>
            <div class="row">
                <span class="label">Date</span>
                <span class="value">{{ $invoiceData->issued_at->format('d M Y') }}</span>
            </div>
            <div class="row" style="margin-top:8px; border-top:1px solid #d1fae5; padding-top:8px;">
                <span class="label" style="font-weight:700; color:#374151;">Amount Paid</span>
                <span class="value" style="color:#006437; font-size:16px;">
                    {{ $invoiceData->currency }} {{ number_format($invoiceData->total, 2) }}
                </span>
            </div>
        </div>

        <p>
            The PDF invoice is attached. Please keep it for your records.
            For any queries regarding this invoice, please contact us at
            <a href="mailto:{{ $invoiceData->business_email }}">{{ $invoiceData->business_email }}</a>.
        </p>

        <p>Thank you for your business!</p>

        <p>
            Warm regards,<br>
            <strong>{{ $invoiceData->business_name }}</strong>
        </p>
    </div>
    <div class="footer">
        &copy; {{ date('Y') }} {{ $invoiceData->business_name }} &bull; KRA PIN: {{ $invoiceData->kra_pin }}<br>
        {{ $invoiceData->business_address }}
        @if ($invoiceData->business_website)
            &bull; <a href="{{ $invoiceData->business_website }}">{{ $invoiceData->business_website }}</a>
        @endif
    </div>
</div>
</body>
</html>
