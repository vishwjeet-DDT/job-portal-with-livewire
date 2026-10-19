<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $companyName }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background-color: #f4f6f9;
            color: #333333;
            line-height: 1.6;
        }

        .wrapper {
            max-width: 620px;
            margin: 40px auto;
            background: #ffffff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }

        /* Header */
        .header {
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            padding: 36px 40px;
            text-align: center;
        }

        .header h1 {
            color: #ffffff;
            font-size: 24px;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        .header p {
            color: rgba(255,255,255,0.8);
            font-size: 14px;
            margin-top: 6px;
        }

        /* Body */
        .body {
            padding: 40px;
        }

        .greeting {
            font-size: 18px;
            font-weight: 600;
            color: #1a1a2e;
            margin-bottom: 16px;
        }

        .message-body {
            font-size: 15px;
            color: #555555;
            line-height: 1.8;
            white-space: pre-line;
        }

        /* Info Card */
        .info-card {
            background: #f8f7ff;
            border-left: 4px solid #4f46e5;
            border-radius: 6px;
            padding: 16px 20px;
            margin: 28px 0;
        }

        .info-card p {
            font-size: 14px;
            color: #444;
            margin-bottom: 6px;
        }

        .info-card p:last-child { margin-bottom: 0; }

        .info-card strong {
            color: #1a1a2e;
        }

        /* Divider */
        .divider {
            border: none;
            border-top: 1px solid #eeeeee;
            margin: 28px 0;
        }

        /* Footer */
        .footer {
            background: #f8f9fb;
            padding: 24px 40px;
            text-align: center;
            border-top: 1px solid #eeeeee;
        }

        .footer p {
            font-size: 12px;
            color: #999999;
            margin-bottom: 4px;
        }

        .footer a {
            color: #4f46e5;
            text-decoration: none;
        }

        @media (max-width: 640px) {
            .wrapper { margin: 0; border-radius: 0; }
            .body, .footer { padding: 28px 24px; }
            .header { padding: 28px 24px; }
        }
    </style>
</head>
<body>

<div class="wrapper">

    {{-- Header --}}
    <div class="header">
        <h1>{{ $companyName }}</h1>
        <p>Recruitment Team</p>
    </div>

    {{-- Body --}}
    <div class="body">

        <p class="greeting">Dear {{ $candidateName }},</p>

        {{-- Dynamic template body --}}
        <p class="message-body">{{ $body }}</p>

        {{-- user Info Card --}}
        <div class="info-card">
            <p><strong>Position:</strong> {{ $jobTitle }}</p>
            <p><strong>Company:</strong> {{ $companyName }}</p>
            <p><strong>Applied On:</strong> {{ $user->created_at->format('d M Y') }}</p>
        </div>

        <hr class="divider">

        <p class="message-body">
            If you have any questions, feel free to reach out to us at
            <a href="mailto:{{ $companyEmail }}" style="color: #4f46e5;">{{ $companyEmail }}</a>.
        </p>

    </div>

    {{-- Footer --}}
    <div class="footer">
        <p>&copy; {{ date('Y') }} {{ $companyName }}. All rights reserved.</p>
        <p>You are receiving this email because you applied for a position at {{ $companyName }}.</p>
    </div>

</div>

</body>
</html>
