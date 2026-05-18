<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #1f2937; font-size: 12px; line-height: 1.55; }
        .header { border-bottom: 2px solid #0a5c4a; padding-bottom: 12px; margin-bottom: 22px; }
        .title { color: #0a5c4a; font-size: 22px; font-weight: bold; margin: 0; }
        .subtitle { color: #64748b; margin-top: 4px; }
        .section { margin-top: 18px; }
        .label { color: #64748b; font-size: 10px; text-transform: uppercase; font-weight: bold; }
        .value { font-size: 13px; font-weight: bold; margin-top: 2px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        td { border: 1px solid #e5e7eb; padding: 8px; vertical-align: top; }
        .status { display: inline-block; background: #ecfdf5; color: #0a5c4a; border: 1px solid #bbf7d0; padding: 5px 9px; font-weight: bold; }
        .footer { margin-top: 28px; padding-top: 12px; border-top: 1px solid #e5e7eb; color: #64748b; font-size: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h1 class="title">Official Service Response</h1>
        <div class="subtitle">{{ $serviceRequest->service->office->name }}</div>
    </div>

    <div class="section">
        <span class="status">Request Completed</span>
    </div>

    <div class="section">
        <table>
            <tr>
                <td>
                    <div class="label">Request ID</div>
                    <div class="value">#{{ $serviceRequest->id }}</div>
                </td>
                <td>
                    <div class="label">Completed On</div>
                    <div class="value">{{ $serviceRequest->updated_at->format('M d, Y h:i A') }}</div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="label">Citizen</div>
                    <div class="value">{{ $serviceRequest->citizen->name }}</div>
                </td>
                <td>
                    <div class="label">Service</div>
                    <div class="value">{{ $serviceRequest->service->name }}</div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="label">Office</div>
                    <div class="value">{{ $serviceRequest->service->office->name }}</div>
                </td>
                <td>
                    <div class="label">Category</div>
                    <div class="value">{{ $serviceRequest->service->category->name }}</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="label">Official Note</div>
        <p>
            This document confirms that the service request listed above has been completed by the municipality office.
            Please keep this response document with your service request records.
        </p>
        @if($serviceRequest->office_notes)
            <p><strong>Office notes:</strong> {{ $serviceRequest->office_notes }}</p>
        @endif
    </div>

    <div class="footer">
        Generated automatically by the E-Services Platform.
        Tracking token: {{ $serviceRequest->qr_code_token }}
    </div>
</body>
</html>
