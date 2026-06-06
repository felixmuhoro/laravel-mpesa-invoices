<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        /* ================================================================
           Base & Typography
           ================================================================ */
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'DejaVu Sans', 'Helvetica Neue', Arial, sans-serif;
            font-size: 13px;
            line-height: 1.6;
            color: #1a1a2e;
            background: #ffffff;
        }

        /* ================================================================
           Page Layout
           ================================================================ */
        .page {
            padding: 40px 48px;
            max-width: 794px;
            margin: 0 auto;
            background: #ffffff;
        }

        /* ================================================================
           Header
           ================================================================ */
        .header {
            display: table;
            width: 100%;
            margin-bottom: 36px;
            border-bottom: 3px solid #006437;
            padding-bottom: 24px;
        }

        .header-left {
            display: table-cell;
            vertical-align: middle;
            width: 55%;
        }

        .header-right {
            display: table-cell;
            vertical-align: middle;
            text-align: right;
            width: 45%;
        }

        .logo-placeholder {
            display: inline-block;
            width: 48px;
            height: 48px;
            background: #006437;
            border-radius: 8px;
            vertical-align: middle;
            margin-right: 12px;
        }

        .business-name {
            font-size: 22px;
            font-weight: 700;
            color: #006437;
            letter-spacing: -0.5px;
            display: inline-block;
            vertical-align: middle;
        }

        .business-tagline {
            font-size: 11px;
            color: #6b7280;
            margin-top: 2px;
        }

        .invoice-label {
            font-size: 30px;
            font-weight: 800;
            color: #006437;
            letter-spacing: -1px;
            line-height: 1;
        }

        .invoice-number-badge {
            display: inline-block;
            background: #f0faf4;
            border: 1px solid #006437;
            color: #006437;
            font-size: 12px;
            font-weight: 700;
            padding: 4px 10px;
            border-radius: 4px;
            margin-top: 6px;
            letter-spacing: 0.5px;
        }

        /* ================================================================
           Meta Row (dates, status)
           ================================================================ */
        .meta-row {
            display: table;
            width: 100%;
            margin-bottom: 32px;
        }

        .meta-cell {
            display: table-cell;
            width: 25%;
            vertical-align: top;
        }

        .meta-label {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #9ca3af;
            font-weight: 600;
            margin-bottom: 3px;
        }

        .meta-value {
            font-size: 13px;
            font-weight: 600;
            color: #1a1a2e;
        }

        /* ================================================================
           Parties (Bill From / Bill To)
           ================================================================ */
        .parties {
            display: table;
            width: 100%;
            margin-bottom: 32px;
            background: #f9fafb;
            border-radius: 8px;
            overflow: hidden;
        }

        .party {
            display: table-cell;
            width: 50%;
            padding: 20px 24px;
            vertical-align: top;
        }

        .party-from {
            border-right: 1px solid #e5e7eb;
        }

        .party-heading {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #006437;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .party-name {
            font-size: 15px;
            font-weight: 700;
            color: #1a1a2e;
            margin-bottom: 4px;
        }

        .party-detail {
            font-size: 12px;
            color: #4b5563;
            line-height: 1.7;
        }

        .party-detail strong {
            color: #374151;
        }

        /* ================================================================
           M-Pesa Receipt Banner
           ================================================================ */
        .mpesa-banner {
            background: linear-gradient(135deg, #006437 0%, #00a651 100%);
            border-radius: 8px;
            padding: 16px 24px;
            margin-bottom: 32px;
            display: table;
            width: 100%;
        }

        .mpesa-banner-left {
            display: table-cell;
            vertical-align: middle;
            width: 70%;
        }

        .mpesa-banner-right {
            display: table-cell;
            vertical-align: middle;
            text-align: right;
            width: 30%;
        }

        .mpesa-label {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: rgba(255,255,255,0.75);
            margin-bottom: 2px;
        }

        .mpesa-receipt-code {
            font-size: 20px;
            font-weight: 800;
            color: #ffffff;
            letter-spacing: 2px;
        }

        .mpesa-logo-text {
            font-size: 18px;
            font-weight: 800;
            color: #ffffff;
            opacity: 0.9;
        }

        .mpesa-logo-sub {
            font-size: 10px;
            color: rgba(255,255,255,0.65);
            margin-top: 2px;
        }

        /* ================================================================
           Items Table
           ================================================================ */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }

        .items-table thead tr {
            background: #006437;
            color: #ffffff;
        }

        .items-table thead th {
            padding: 10px 14px;
            text-align: left;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }

        .items-table thead th.text-right {
            text-align: right;
        }

        .items-table tbody tr {
            border-bottom: 1px solid #f3f4f6;
        }

        .items-table tbody tr:nth-child(even) {
            background: #f9fafb;
        }

        .items-table tbody td {
            padding: 12px 14px;
            font-size: 13px;
            color: #374151;
            vertical-align: top;
        }

        .items-table tbody td.text-right {
            text-align: right;
        }

        .item-description {
            font-weight: 600;
            color: #1a1a2e;
        }

        /* ================================================================
           Totals
           ================================================================ */
        .totals-wrapper {
            display: table;
            width: 100%;
            margin-bottom: 32px;
        }

        .totals-spacer {
            display: table-cell;
            width: 55%;
        }

        .totals-box {
            display: table-cell;
            width: 45%;
            vertical-align: top;
        }

        .totals-table {
            width: 100%;
            border-collapse: collapse;
        }

        .totals-table td {
            padding: 6px 0;
            font-size: 13px;
        }

        .totals-table .totals-label {
            color: #6b7280;
            text-align: left;
        }

        .totals-table .totals-value {
            text-align: right;
            font-weight: 600;
            color: #374151;
        }

        .totals-table .totals-separator td {
            border-top: 1px solid #e5e7eb;
            padding-top: 10px;
        }

        .totals-table .total-row td {
            font-size: 16px;
            font-weight: 800;
            color: #006437;
            padding-top: 8px;
            border-top: 2px solid #006437;
        }

        /* ================================================================
           Notes / Compliance Block
           ================================================================ */
        .compliance-block {
            background: #f0faf4;
            border-left: 4px solid #006437;
            border-radius: 0 6px 6px 0;
            padding: 14px 18px;
            margin-bottom: 32px;
            font-size: 11px;
            color: #374151;
            line-height: 1.7;
        }

        .compliance-block strong {
            color: #006437;
        }

        /* ================================================================
           Footer
           ================================================================ */
        .footer {
            border-top: 1px solid #e5e7eb;
            padding-top: 20px;
            display: table;
            width: 100%;
        }

        .footer-left {
            display: table-cell;
            width: 60%;
            vertical-align: middle;
            font-size: 11px;
            color: #9ca3af;
            line-height: 1.6;
        }

        .footer-right {
            display: table-cell;
            width: 40%;
            vertical-align: middle;
            text-align: right;
        }

        .paid-stamp {
            display: inline-block;
            border: 3px solid #006437;
            border-radius: 6px;
            padding: 6px 14px;
            font-size: 16px;
            font-weight: 800;
            color: #006437;
            letter-spacing: 3px;
            text-transform: uppercase;
            opacity: 0.85;
            transform: rotate(-5deg);
        }

        /* ================================================================
           Utility
           ================================================================ */
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: 700; }
        .text-green { color: #006437; }
        .text-muted { color: #9ca3af; }
        .mt-4 { margin-top: 4px; }
        .mt-8 { margin-top: 8px; }
    </style>
</head>
<body>
<div class="page">

    {{-- ================================================================ --}}
    {{-- HEADER                                                           --}}
    {{-- ================================================================ --}}
    <div class="header">
        <div class="header-left">
            @if ($invoice->logo_path && file_exists($invoice->logo_path))
                <img src="{{ $invoice->logo_path }}" alt="{{ $invoice->business_name }}" style="height:48px; vertical-align:middle; margin-right:12px;">
            @else
                <div class="logo-placeholder"></div>
            @endif
            <span class="business-name">{{ $invoice->business_name }}</span>
            @if ($invoice->business_website)
                <div class="business-tagline">{{ $invoice->business_website }}</div>
            @endif
        </div>
        <div class="header-right">
            <div class="invoice-label">INVOICE</div>
            <div class="invoice-number-badge">{{ $invoice->invoice_number }}</div>
        </div>
    </div>

    {{-- ================================================================ --}}
    {{-- META ROW                                                         --}}
    {{-- ================================================================ --}}
    <div class="meta-row">
        <div class="meta-cell">
            <div class="meta-label">Issue Date</div>
            <div class="meta-value">{{ $invoice->issued_at->format('d M Y') }}</div>
        </div>
        <div class="meta-cell">
            <div class="meta-label">Currency</div>
            <div class="meta-value">{{ $invoice->currency }}</div>
        </div>
        <div class="meta-cell">
            <div class="meta-label">KRA PIN</div>
            <div class="meta-value">{{ $invoice->kra_pin }}</div>
        </div>
        @if ($invoice->vat_number)
        <div class="meta-cell">
            <div class="meta-label">VAT Number</div>
            <div class="meta-value">{{ $invoice->vat_number }}</div>
        </div>
        @endif
    </div>

    {{-- ================================================================ --}}
    {{-- BILL FROM / BILL TO                                              --}}
    {{-- ================================================================ --}}
    <div class="parties">
        <div class="party party-from">
            <div class="party-heading">Bill From</div>
            <div class="party-name">{{ $invoice->business_name }}</div>
            <div class="party-detail">
                {{ $invoice->business_address }}<br>
                <strong>Phone:</strong> {{ $invoice->business_phone }}<br>
                <strong>Email:</strong> {{ $invoice->business_email }}<br>
                <strong>KRA PIN:</strong> {{ $invoice->kra_pin }}
                @if ($invoice->vat_number)
                    <br><strong>VAT Reg No:</strong> {{ $invoice->vat_number }}
                @endif
            </div>
        </div>
        <div class="party">
            <div class="party-heading">Bill To</div>
            <div class="party-name">{{ $invoice->customer_name }}</div>
            <div class="party-detail">
                <strong>Phone:</strong> {{ $invoice->customer_phone }}<br>
                @if ($invoice->customer_email)
                    <strong>Email:</strong> {{ $invoice->customer_email }}
                @endif
            </div>
        </div>
    </div>

    {{-- ================================================================ --}}
    {{-- M-PESA RECEIPT BANNER                                            --}}
    {{-- ================================================================ --}}
    <div class="mpesa-banner">
        <div class="mpesa-banner-left">
            <div class="mpesa-label">M-Pesa Transaction Confirmation</div>
            <div class="mpesa-receipt-code">{{ $invoice->mpesa_receipt }}</div>
        </div>
        <div class="mpesa-banner-right">
            <div class="mpesa-logo-text">M-PESA</div>
            <div class="mpesa-logo-sub">Powered by Safaricom</div>
        </div>
    </div>

    {{-- ================================================================ --}}
    {{-- ITEMS TABLE                                                       --}}
    {{-- ================================================================ --}}
    <table class="items-table">
        <thead>
            <tr>
                <th style="width:50%">Description</th>
                <th style="width:15%" class="text-right">Qty</th>
                <th style="width:17%" class="text-right">Unit Price</th>
                <th style="width:18%" class="text-right">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($invoice->items as $item)
            <tr>
                <td><span class="item-description">{{ $item->description }}</span></td>
                <td class="text-right">{{ number_format($item->quantity, 0) }}</td>
                <td class="text-right">{{ $invoice->currency }} {{ number_format($item->unit_price, 2) }}</td>
                <td class="text-right">{{ $invoice->currency }} {{ number_format($item->amount, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- ================================================================ --}}
    {{-- TOTALS                                                           --}}
    {{-- ================================================================ --}}
    <div class="totals-wrapper">
        <div class="totals-spacer"></div>
        <div class="totals-box">
            <table class="totals-table">
                <tr>
                    <td class="totals-label">Subtotal</td>
                    <td class="totals-value">{{ $invoice->currency }} {{ number_format($invoice->subtotal, 2) }}</td>
                </tr>
                @if ($invoice->tax > 0)
                <tr>
                    <td class="totals-label">{{ $invoice->tax_label }}</td>
                    <td class="totals-value">{{ $invoice->currency }} {{ number_format($invoice->tax, 2) }}</td>
                </tr>
                @endif
                <tr class="total-row">
                    <td>Total Due</td>
                    <td>{{ $invoice->currency }} {{ number_format($invoice->total, 2) }}</td>
                </tr>
            </table>
        </div>
    </div>

    {{-- ================================================================ --}}
    {{-- COMPLIANCE NOTES                                                 --}}
    {{-- ================================================================ --}}
    <div class="compliance-block">
        <strong>Payment Confirmation:</strong>
        This invoice serves as an official receipt for the M-Pesa payment received.
        Reference: <strong>{{ $invoice->mpesa_receipt }}</strong>.
        Amount paid: <strong>{{ $invoice->currency }} {{ number_format($invoice->total, 2) }}</strong>
        on <strong>{{ $invoice->issued_at->format('d F Y \a\t H:i') }}</strong>.
        <br>
        <strong>Tax Compliance:</strong> This invoice is issued in compliance with the Kenya Revenue Authority (KRA)
        requirements. KRA PIN: <strong>{{ $invoice->kra_pin }}</strong>.
        @if ($invoice->vat_number)
            VAT Registration No: <strong>{{ $invoice->vat_number }}</strong>.
            This invoice includes VAT as per the Value Added Tax Act (Cap. 476).
        @endif
    </div>

    {{-- ================================================================ --}}
    {{-- FOOTER                                                           --}}
    {{-- ================================================================ --}}
    <div class="footer">
        <div class="footer-left">
            <strong>{{ $invoice->business_name }}</strong><br>
            {{ $invoice->business_address }}<br>
            {{ $invoice->business_phone }} &bull; {{ $invoice->business_email }}
            @if ($invoice->business_website)
                &bull; {{ $invoice->business_website }}
            @endif
            <div class="mt-4 text-muted">Generated on {{ now()->format('d M Y H:i') }} &bull; {{ $invoice->invoice_number }}</div>
        </div>
        <div class="footer-right">
            <div class="paid-stamp">PAID</div>
        </div>
    </div>

</div>
</body>
</html>
