<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $coach->role }} {{ $coach->firstname }} {{ $coach->middlename }} {{ $coach->lastname }} Agreement Contract</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{-- Local icons (optional for PDF readers; safe to keep) --}}
    <link rel="icon" href="/image/wc/logo.png" type="image/png" sizes="512x512">
    <link rel="apple-touch-icon" href="/image/wc/logo.png" sizes="180x180">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* PDF-safe CSS (no external CSS/JS) */
        html, body { margin:0; padding:0; }
        body {
            font-family: DejaVu Sans, Arial, Helvetica, sans-serif;
            font-size: 12px;
            line-height: 1.6;
            color: #111;
            background: #f5f5f5;
        }
        .container {
            background:#fff;
            max-width: 850px;
            margin: 24px auto;
            padding: 32px 36px;
            box-shadow: 0 1px 3px rgba(0,0,0,.08);
        }
        h2 {
            font-size: 22px;
            font-weight: 700;
            margin: 0 0 18px;
            text-align: center;
            text-decoration: underline;
        }
        .meta { margin: 0 0 18px; }
        .meta p { margin: 4px 0; }
        p { text-align: justify; margin: 0 0 12px; }
        .section-title {
            font-weight: 700;
            margin: 18px 0 8px;
            text-transform: uppercase;
        }

        .grid-3 {
            display: table;
            width: 100%;
            border-collapse: separate;
            border-spacing: 12px 0;
        }
        .grid-3 .card {
            display: table-cell;
            width: 33.333%;
            vertical-align: top;
            border: 1px solid #ddd;
            border-radius: 10px;
            padding: 10px 12px;
            background: #fff;
        }
        .card .label {
            font-size: 10px;
            text-transform: uppercase;
            color: #666;
            letter-spacing: .5px;
        }
        .card .value {
            margin-top: 4px;
            font-weight: 600;
        }

        .signatures {
            display: table;
            width: 100%;
            margin-top: 28px;
            border-collapse: separate;
            border-spacing: 16px 0;
        }
        .signature-block {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            text-align: center;
            padding: 14px;
            border: 1px solid #ddd;
            border-radius: 12px;
            background: #fff;
        }
        .signature-img {
            max-width: 220px;
            max-height: 80px;
            margin: 0 auto 8px;
            display: block;
        }
        .line {
            height: 30px;
            border-bottom: 1.5px solid #222;
            margin: 0 18px 6px;
        }
        .full-name {
            font-weight: 600;
            margin-top: 2px;
        }
        .sig-label {
            margin-top: 2px;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 11px;
            color: #333;
        }
    </style>
</head>
<body>
    <div class="container">

        {{-- Header --}}
        <h2>{{ strtoupper($coach->role) }} AGREEMENT CONTRACT</h2>

        {{-- Parties Info (compact cards) --}}
        <div class="grid-3" style="margin-bottom:14px;">
            <div class="card">
                <div class="label">Coach</div>
                <div class="value">{{ $coach->firstname }} {{ $coach->middlename }} {{ $coach->lastname }}</div>
            </div>
            <div class="card">
                <div class="label">Client</div>
                <div class="value">{{ $client_name ?? '______________________' }}</div>
            </div>
            <div class="card">
                <div class="label">Date</div>
                <div class="value">{{ $date ?? now()->format('F d, Y') }}</div>
            </div>
        </div>

        {{-- Purpose / Terms --}}
        <div class="section-title">Purpose of Agreement</div>

        <p>
            This Agreement outlines the terms under which the <strong>{{ $coach->role }}</strong> will provide professional services to the Client.
            It ensures clarity, safety, mutual understanding, and accountability in the coaching relationship. The {{ $coach->role }} agrees to provide a safe,
            supportive, and professional environment, to maintain confidentiality of all client communications, and to deliver services to the best of their ability
            within the agreed scope.
        </p>

        <p>
            The Client agrees to attend scheduled sessions on time, communicate openly and honestly, and take full responsibility for their own decisions, actions, and results.
            The services provided under this Agreement include: <strong>{{ $coach->talents ?? '__________' }}</strong>.
            The service fee is ₱<strong>{{ $coach->service_fee ?? $coach->appointment_price ?? '______' }}</strong> per session or package.
            Each session will last for <strong>{{ $coach->duration ?? $coach->session_duration ?? '______' }}</strong>.
            Payment will be made by <strong>{{ $coach->payment ?? $coach->payment_method ?? '__________' }}</strong>. Payment must be made in advance unless otherwise agreed.
        </p>

        <p>
            All sessions must be scheduled in advance. A minimum notice of
            <strong>{{ $coach->notice_hours ?? 0 }}</strong> hours /
            <strong>{{ $coach->notice_days ?? 0 }}</strong> days
            is required for cancellations or rescheduling. Notice of cancellation must be provided by
            <strong>{{ $coach->method ?? $coach->cancellation_method ?? '__________' }}</strong>.
            If the Client cancels on the same day of the scheduled session, the Client agrees to pay
            <strong>25% of the service fee</strong>. Failure to provide any notice (“no show”) will result in the session being charged in full.
        </p>

        <p>
            By entering into this Agreement, both the {{ $coach->role }} and the Client acknowledge their shared commitment to a respectful,
            professional, and productive working relationship. This Agreement is intended to protect both parties and ensure that services are
            delivered with integrity and accountability. By signing below, both parties confirm that they have read, understood, and agreed to
            the terms outlined in this Agreement.
        </p>

        {{-- Signatures --}}
        <div class="signatures">

            {{-- Coach signature with fallbacks: dataURL -> stored path -> profile signature -> line --}}
            <div class="signature-block">
                <div class="full-name" style="margin-bottom:8px;"><strong>{{ $coach->role }} Signature:</strong></div>

                @php
                    // Build a local filesystem path for the coach profile signature for dompdf
                    $profileSigPath = !empty($coach->signature) ? public_path('storage/'.$coach->signature) : null;
                @endphp

                @if (!empty($coachSignatureDataUrl))
                    {{-- freshly drawn (data URL) --}}
                    <img src="{{ $coachSignatureDataUrl }}" alt="Coach Signature" class="signature-img">
                @elseif (!empty($coachSignatureStorageUrl))
                    {{-- path/url of newly stored sig (controller provided). If it's a local file path, dompdf can still render it. --}}
                    <img src="{{ $coachSignatureStorageUrl }}" alt="Coach Signature" class="signature-img">
                @elseif (!empty($profileSigPath) && file_exists($profileSigPath))
                    {{-- final fallback to coach profile signature (local path for dompdf) --}}
                    <img src="{{ $profileSigPath }}" alt="Coach Signature" class="signature-img">
                @else
                    <div class="line"></div>
                @endif

                <div class="full-name">{{ $coach->firstname }} {{ $coach->middlename }} {{ $coach->lastname }}</div>
                <div class="sig-label">Coach's Signature</div>
            </div>

            {{-- Client signature (placeholder; wire up your own data if available) --}}
            <div class="signature-block">
                <div class="full-name" style="margin-bottom:8px;"><strong>Client Signature:</strong></div>

                @php
                    // Optional: if you pass $clientSignatureDataUrl or $clientSignaturePath, show it here.
                    $clientSigShown = false;
                @endphp

                @if (!empty($clientSignatureDataUrl))
                    <img src="{{ $clientSignatureDataUrl }}" alt="Client Signature" class="signature-img">
                    @php $clientSigShown = true; @endphp
                @elseif (!empty($clientSignatureStorageUrl))
                    <img src="{{ $clientSignatureStorageUrl }}" alt="Client Signature" class="signature-img">
                    @php $clientSigShown = true; @endphp
                @endif

                @if (!$clientSigShown)
                    <div class="line"></div>
                @endif

                <div class="full-name">{{ $client_name ?? '______________________' }}</div>
                <div class="sig-label">Client's Signature</div>
            </div>

        </div>

    </div>
</body>
</html>
