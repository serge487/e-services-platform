<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 13px; color: #1f2937; background: #fff; padding: 40px; }

        .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 32px; border-bottom: 3px solid #0a5c4a; padding-bottom: 20px; }
        .brand { font-size: 20px; font-weight: 700; color: #0a5c4a; }
        .brand small { display: block; font-size: 11px; color: #64748b; font-weight: 400; margin-top: 2px; }
        .invoice-title { text-align: right; }
        .invoice-title h1 { font-size: 26px; color: #0a5c4a; font-weight: 700; }
        .invoice-title p { font-size: 11px; color: #64748b; margin-top: 4px; }

        .section { margin-bottom: 24px; }
        .section-title { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #0a5c4a; margin-bottom: 10px; border-bottom: 1px solid #d9eee7; padding-bottom: 4px; }

        .grid-2 { width: 100%; }
        .grid-2 td { width: 50%; vertical-align: top; padding: 4px 0; }

        .label { font-size: 10px; color: #64748b; text-transform: uppercase; letter-spacing: 0.3px; }
        .value { font-size: 13px; font-weight: 600; color: #1f2937; margin-top: 1px; }

        .items-table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        .items-table th { background: #0a5c4a; color: #fff; padding: 8px 12px; text-align: left; font-size: 11px; text-transform: uppercase; letter-spacing: 0.4px; }
        .items-table td { padding: 10px 12px; border-bottom: 1px solid #f1f5f9; font-size: 12px; }
        .items-table tr:last-child td { border-bottom: none; }

        .total-box { background: #ecfdf5; border: 1px solid #d9eee7; border-radius: 8px; padding: 14px 18px; margin-top: 16px; text-align: right; }
        .total-box .total-label { font-size: 11px; color: #64748b; }
        .total-box .total-amount { font-size: 22px; font-weight: 700; color: #0a5c4a; }

        .status-paid { display: inline-block; background: #ecfdf5; color: #0a5c4a; border: 1px solid #d9eee7; border-radius: 20px; padding: 3px 12px; font-size: 11px; font-weight: 700; }

        .footer { margin-top: 40px; border-top: 1px solid #e2e8f0; padding-top: 16px; text-align: center; font-size: 10px; color: #94a3b8; }
    </style>
</head>
<body>

    <div class="header">
        <div>
            <div class="brand">
                🏛️ E-Services Portal
                <small>{{ $serviceRequest->service->office->name }}</small>
            </div>
        </div>
        <div class="invoice-title">
            <h1>INVOICE</h1>
            <p>Invoice #INV-{{ str_pad($serviceRequest->id, 6, '0', STR_PAD_LEFT) }}</p>
            <p>{{ $serviceRequest->payment->paid_at?->format('M d, Y') }}</p>
            <span class="status-paid">✓ PAID</span>
        </div>
    </div>

    {{-- Citizen Info --}}
    <div class="section">
        <div class="section-title">Billed To</div>
        <table class="grid-2">
            <tr>
                <td>
                    <div class="label">Full Name</div>
                    <div class="value">{{ $serviceRequest->citizen->name }}</div>
                </td>
                <td>
                    <div class="label">Email</div>
                    <div class="value">{{ $serviceRequest->citizen->email }}</div>
                </td>
            </tr>
        </table>
    </div>

    {{-- Request Info --}}
    <div class="section">
        <div class="section-title">Request Details</div>
        <table class="grid-2">
            <tr>
                <td>
                    <div class="label">Request ID</div>
                    <div class="value">#{{ $serviceRequest->id }}</div>
                </td>
                <td>
                    <div class="label">Submitted On</div>
                    <div class="value">{{ $serviceRequest->created_at->format('M d, Y') }}</div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="label">Service</div>
                    <div class="value">{{ $serviceRequest->service->name }}</div>
                </td>
                <td>
                    <div class="label">Office</div>
                    <div class="value">{{ $serviceRequest->service->office->name }}</div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="label">Status</div>
                    <div class="value">{{ $serviceRequest->status }}</div>
                </td>
                <td>
                    <div class="label">Payment Method</div>
                    <div class="value">{{ $serviceRequest->payment->methodLabel() }}</div>
                </td>
            </tr>
        </table>
    </div>

    {{-- Line items --}}
    <div class="section">
        <div class="section-title">Payment Summary</div>
        <table class="items-table">
            <thead>
                <tr>
                    <th>Description</th>
                    <th style="text-align:right;">Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ $serviceRequest->service->name }} — Service Fee</td>
                    <td style="text-align:right;">${{ number_format($serviceRequest->payment->amount, 2) }}</td>
                </tr>
            </tbody>
        </table>

        <div class="total-box">
            <div class="total-label">Total Paid</div>
            <div class="total-amount">${{ number_format($serviceRequest->payment->amount, 2) }} {{ $serviceRequest->payment->currency }}</div>
        </div>
    </div>

    <div class="footer">
        This invoice was generated automatically by the E-Services Portal. 
        For questions contact the office at {{ $serviceRequest->service->office->name }}.
        &nbsp;·&nbsp; Request Token: {{ $serviceRequest->qr_code_token }}
    </div>

</body>
</html>