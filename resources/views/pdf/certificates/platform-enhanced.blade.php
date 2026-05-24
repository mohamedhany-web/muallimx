@php
    $academy = $academyName ?? config('app.name', 'Muallimx');
    $brandSlug = strtoupper(preg_replace('/\s+/', '', $academy));
    $primary = '#1B2C6E';
    $orange = '#E84E0E';
    $gold = '#C9A84C';
    $cream = '#FAFAF8';
    $navyDark = '#111E4E';
@endphp
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    @if(!empty($previewWatermark))
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
    @endif
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            margin: 0;
            padding: 0;
            background: {{ $cream }};
            color: {{ $navyDark }};
        }
        .wrap {
            border: 2px solid {{ $gold }};
            background: #fffefb;
            min-height: 175mm;
        }
        .top-band {
            background: {{ $primary }};
            color: #fff;
            padding: 10mm 12mm;
        }
        .top-band table { width: 100%; border-collapse: collapse; }
        .top-band td { vertical-align: middle; }
        .logo-img {
            width: 18mm;
            height: 18mm;
            border-radius: 4mm;
            object-fit: contain;
        }
        .brand-name {
            font-size: 16pt;
            font-weight: bold;
            letter-spacing: 1px;
        }
        .brand-sub {
            font-size: 7pt;
            color: #E8C96A;
            margin-top: 1mm;
        }
        .cert-type {
            font-size: 7pt;
            color: #E8C96A;
            text-align: left;
        }
        .cert-no {
            font-size: 9pt;
            color: #ddd;
            text-align: left;
            margin-top: 1mm;
        }
        .gold-line {
            height: 2pt;
            min-height: 2pt;
            background: {{ $gold }};
        }
        .body {
            padding: 10mm 14mm 8mm;
            background: {{ $cream }};
            position: relative;
        }
        .wm {
            position: absolute;
            top: 35mm;
            left: 50%;
            margin-left: -35mm;
            width: 70mm;
            opacity: 0.05;
        }
        .eyebrow {
            text-align: center;
            font-size: 9pt;
            color: {{ $orange }};
            font-weight: bold;
            margin-bottom: 3mm;
        }
        h1 {
            text-align: center;
            font-size: 24pt;
            font-weight: bold;
            color: {{ $navyDark }};
            margin: 0 0 2mm;
        }
        .sub {
            text-align: center;
            font-size: 9pt;
            color: #666;
            margin-bottom: 5mm;
        }
        .badge {
            text-align: center;
            font-size: 8pt;
            font-weight: bold;
            color: {{ $navyDark }};
            border: 1px solid rgba(232,78,14,0.25);
            background: #FFF5F0;
            padding: 2mm 5mm;
            display: inline-block;
            margin: 0 auto 5mm;
        }
        .divider {
            width: 50mm;
            height: 1pt;
            min-height: 1pt;
            background: {{ $gold }};
            margin: 0 auto 6mm;
        }
        .label {
            text-align: center;
            font-size: 9pt;
            color: #888;
            margin-bottom: 2mm;
        }
        .student {
            text-align: center;
            font-size: 22pt;
            font-weight: bold;
            color: {{ $primary }};
            margin-bottom: 2mm;
        }
        .student-line {
            width: 45mm;
            height: 1.5pt;
            min-height: 1.5pt;
            background: {{ $orange }};
            margin: 0 auto 6mm;
        }
        .course {
            text-align: center;
            font-size: 14pt;
            font-weight: bold;
            color: {{ $primary }};
            margin-bottom: 2mm;
        }
        .text {
            text-align: center;
            font-size: 10pt;
            color: #555;
            line-height: 1.6;
            margin-bottom: 6mm;
        }
        .meta {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid rgba(201,168,76,0.35);
            background: #fff;
            margin-bottom: 7mm;
        }
        .meta td {
            width: 25%;
            text-align: center;
            vertical-align: top;
            padding: 3mm 2mm;
            border-left: 1px solid rgba(201,168,76,0.2);
        }
        .meta td:first-child { border-left: none; }
        .meta-label { font-size: 7pt; color: #999; margin-bottom: 1mm; }
        .meta-value { font-size: 9pt; font-weight: bold; color: {{ $navyDark }}; }
        .footer {
            width: 100%;
            border-collapse: collapse;
            border-top: 1px solid rgba(201,168,76,0.25);
            margin-top: 2mm;
        }
        .footer td {
            width: 25%;
            text-align: center;
            vertical-align: bottom;
            padding: 4mm 2mm 0;
        }
        .sign-name {
            font-size: 11pt;
            font-style: italic;
            color: {{ $navyDark }};
            border-bottom: 1px solid {{ $navyDark }};
            padding-bottom: 1mm;
            margin-bottom: 1mm;
        }
        .sign-title { font-size: 7pt; color: #888; }
        .seal {
            width: 22mm;
            height: 22mm;
            border: 2px solid {{ $gold }};
            border-radius: 50%;
            background: {{ $primary }};
            color: #E8C96A;
            font-size: 6pt;
            text-align: center;
            margin: 0 auto;
            padding-top: 5mm;
            line-height: 1.3;
        }
        .seal-year {
            font-size: 9pt;
            color: #fff;
            font-weight: bold;
            margin-top: 1mm;
        }
        .qr-box {
            border: 1px solid rgba(201,168,76,0.4);
            padding: 1mm;
            background: #fff;
            display: inline-block;
        }
        .qr-label { font-size: 7pt; color: #888; margin-top: 1mm; }
        .bottom-strip {
            height: 3mm;
            min-height: 3mm;
            background: {{ $orange }};
        }
        .preview-wm {
            position: absolute;
            inset: 0;
            text-align: center;
            padding-top: 70mm;
            font-size: 40pt;
            color: rgba(27,44,110,0.08);
            font-weight: bold;
            transform: rotate(-24deg);
        }
    </style>
</head>
<body>
<div class="wrap">
    @if(!empty($previewWatermark))
        <div class="preview-wm">معاينة</div>
    @endif

    <div class="top-band">
        <table>
            <tr>
                <td style="width:70%;">
                    <table>
                        <tr>
                            <td style="width:22mm;">
                                @if(!empty($logoDataUri))
                                    <img class="logo-img" src="{{ $logoDataUri }}" alt="">
                                @endif
                            </td>
                            <td>
                                <div class="brand-name">{{ $brandSlug }}</div>
                                <div class="brand-sub">منصة التعلّم الذكي والشهادات الرقمية</div>
                            </td>
                        </tr>
                    </table>
                </td>
                <td style="width:30%;">
                    <div class="cert-type">Certificate of Completion</div>
                    <div class="cert-no">{{ $certificateNumber }}</div>
                </td>
            </tr>
        </table>
    </div>
    <div class="gold-line"></div>

    <div class="body">
        @if(!empty($watermarkDataUri))
            <img class="wm" src="{{ $watermarkDataUri }}" alt="">
        @endif

        <div class="eyebrow">✦ شهادة إتمام رسمية ✦</div>
        <h1>شهادة إتمام معتمدة</h1>
        <div class="sub">Certificate of Completion · وثيقة تقدير قابلة للتحقق الرقمي</div>
        <div style="text-align:center;">
            <span class="badge">✓ شهادة رقمية موثقة</span>
        </div>
        <div class="divider"></div>

        <div class="label">يشهد بأن</div>
        <div class="student">{{ $studentName }}</div>
        <div class="student-line"></div>

        <div class="text">قد أتمَّ بنجاح متطلبات</div>
        <div class="course">{{ $courseDisplayName }}</div>
        <div class="text">المُقدَّم عبر منصة <strong style="color:{{ $orange }};">{{ $brandSlug }}</strong></div>

        <table class="meta">
            <tr>
                <td>
                    <div class="meta-label">تاريخ الإصدار</div>
                    <div class="meta-value">{{ $issueDateFormatted }}</div>
                </td>
                <td>
                    <div class="meta-label">المدرب</div>
                    <div class="meta-value">{{ $instructorName }}</div>
                </td>
                <td>
                    <div class="meta-label">مدة الدورة</div>
                    <div class="meta-value">{{ $courseDurationLabel ?? '—' }}</div>
                </td>
                <td>
                    <div class="meta-label">رمز التحقق</div>
                    <div class="meta-value" style="font-size:7pt;">{{ $verificationCode }}</div>
                </td>
            </tr>
        </table>

        <table class="footer">
            <tr>
                <td>
                    <div class="sign-name">{{ $instructorName }}</div>
                    <div class="sign-title">{{ $instructorTitle }}</div>
                </td>
                <td>
                    <div class="seal">
                        {{ $brandSlug }}<br>CERTIFIED<br>
                        <span style="color:#C9A84C;">★</span>
                        <div class="seal-year">{{ $issueYear }}</div>
                    </div>
                </td>
                <td>
                    @if(!empty($qrDataUri))
                        <div class="qr-box">
                            <img src="{{ $qrDataUri }}" alt="QR" style="width:20mm;height:20mm;display:block;">
                        </div>
                        <div class="qr-label">امسح للتحقق</div>
                    @elseif(!empty($verificationCode))
                        <div class="meta-value" style="font-size:7pt;">{{ $verificationCode }}</div>
                    @endif
                </td>
                <td>
                    <div class="sign-name">{{ $directorName }}</div>
                    <div class="sign-title">{{ $directorTitle }}</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="bottom-strip"></div>
</div>
</body>
</html>
