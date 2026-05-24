{{-- تصميم الشهادة المعتمد — MUALLIMX Enhanced --}}
@php
    $studentName = $studentName ?? ($certificate->user->name ?? 'الطالب');
    $courseTitle = $courseTitle ?? ($certificate->title ?? $certificate->course_name ?? 'شهادة الإتمام');
    $courseName = $courseName ?? ($certificate->course->title ?? $certificate->course_name ?? $courseTitle);
    $certificateNumber = $certificateNumber ?? ($certificate->certificate_number ?? '');
    $issueDate = $issueDate ?? (
        $certificate->issued_at
            ? $certificate->issued_at->format('Y/m/d')
            : ($certificate->issue_date ? $certificate->issue_date->format('Y/m/d') : '')
    );
    $issueYear = $issueYear ?? (
        $certificate->issued_at
            ? $certificate->issued_at->format('Y')
            : ($certificate->issue_date ? $certificate->issue_date->format('Y') : date('Y'))
    );
    $verificationCode = $verificationCode ?? ($certificate->verification_code ?? '');
    $verificationUrl = $verificationUrl ?? ($certificate->verification_url ?? route('public.certificates.verify', ['code' => $verificationCode]));
    $academyName = $academyName ?? (trim((string) config('certificates.academy_name', '')) ?: config('app.name', 'Muallimx'));
    $brandSlug = strtoupper(preg_replace('/\s+/', '', $academyName));
    $instructorSignatureName = $instructorSignatureName ?? ($certificate->instructor_signature_name ?? ($certificate->instructor->name ?? 'المدرب المعتمد'));
    $instructorSignatureTitle = $instructorSignatureTitle ?? ($certificate->instructor_signature_title ?? 'المدرب / المعلم');
    $academySignatureName = $academySignatureName ?? ($certificate->academy_signature_name ?? config('certificates.director_name', 'المدير العام'));
    $academySignatureTitle = $academySignatureTitle ?? ($certificate->academy_signature_title ?? $academyName);
    $courseDuration = $courseDuration ?? null;
    if ($courseDuration === null && isset($certificate->course)) {
        $hours = (int) ($certificate->course->duration_hours ?? 0);
        $mins = (int) ($certificate->course->duration_minutes ?? 0);
        if ($hours > 0 || $mins > 0) {
            $courseDuration = $hours > 0 ? ($hours.' ساعة') : ($mins.' دقيقة');
        }
    }
    $logoUrl = $logoUrl ?? asset('images/certificates/enhanced-1.jpeg');
    $watermarkUrl = $watermarkUrl ?? asset('images/certificates/enhanced-1.jpeg');
    $showPreviewWatermark = $showPreviewWatermark ?? false;
    $qrElementId = 'mx-qr-'.preg_replace('/[^a-zA-Z0-9_-]/', '', $verificationCode ?: 'preview');
@endphp

@include('components.certificate-enhanced-styles')

<div class="mx-cert-enhanced certificate-print" id="certificate-enhanced">
    <div class="certificate">
        @if($showPreviewWatermark)
            <div class="preview-watermark"><span>معاينة</span></div>
        @endif

        <div class="top-band">
            <div class="brand-logo">
                <div class="logo-icon">
                    <img src="{{ $logoUrl }}" alt="{{ $brandSlug }}">
                </div>
                <div>
                    <div class="brand-name">{{ $brandSlug }}</div>
                    <div class="brand-sub">منصة التعلّم الذكي والشهادات الرقمية</div>
                </div>
            </div>
            <div style="text-align:left; position:relative; z-index:1;">
                <div class="cert-type-label">Certificate of Completion</div>
                @if($certificateNumber)
                    <div class="cert-number">{{ $certificateNumber }}</div>
                @endif
            </div>
            <div class="top-band-line"></div>
        </div>

        <div class="cert-body">
            <img src="{{ $watermarkUrl }}" alt="" class="cert-body-watermark" aria-hidden="true">

            <div class="cert-header">
                <div class="cert-header-eyebrow">✦ شهادة إتمام رسمية ✦</div>
                <div class="cert-header-title">شهادة إتمام معتمدة</div>
                <div class="cert-header-sub">Certificate of Completion · وثيقة تقدير قابلة للتحقق الرقمي</div>
                <div class="header-badge">✓ شهادة رقمية موثقة · QR Verification Ready</div>
            </div>

            <div class="divider">
                <div class="divider-line"></div>
                <div class="divider-diamond"></div>
                <div class="divider-line"></div>
            </div>

            <div class="recipient-section">
                <div class="recipient-label">يشهد بأن</div>
                <div class="recipient-name">{{ $studentName }}</div>
                <div class="recipient-underline"></div>
            </div>

            <div class="info-section">
                <div class="completion-text">قد أتمَّ بنجاح متطلبات</div>
                <div class="course-title">{{ $courseName }}</div>
                <div class="completion-text" style="margin-top:8px;">
                    المُقدَّم عبر منصة <span>{{ $brandSlug }}</span>
                </div>
            </div>

            <div class="details-row">
                <div class="details-row-inner">
                    @if($issueDate)
                    <div class="detail-item">
                        <span class="detail-label">تاريخ الإصدار</span>
                        <span class="detail-value">{{ $issueDate }}</span>
                    </div>
                    @endif
                    <div class="detail-item">
                        <span class="detail-label">المدرب</span>
                        <span class="detail-value">{{ $instructorSignatureName }}</span>
                    </div>
                    @if($courseDuration)
                    <div class="detail-item">
                        <span class="detail-label">مدة الدورة</span>
                        <span class="detail-value">{{ $courseDuration }}</span>
                    </div>
                    @endif
                    @if($verificationCode)
                    <div class="detail-item">
                        <span class="detail-label">رمز التحقق</span>
                        <span class="detail-value" style="font-size:10px; font-family:monospace;">{{ $verificationCode }}</span>
                    </div>
                    @endif
                </div>
            </div>

            <div class="cert-footer">
                <div class="cert-footer-row">
                    <div class="signature-block">
                        <div class="signature-name">{{ $instructorSignatureName }}</div>
                        <div class="signature-label">{{ $instructorSignatureTitle }}</div>
                    </div>
                    <div class="seal">
                        <div class="seal-circle">
                            <div class="seal-text-main">{{ $brandSlug }}<br>CERTIFIED</div>
                            <div class="seal-star">★</div>
                            <div class="seal-year">{{ $issueYear }}</div>
                        </div>
                    </div>
                    <div class="qr-block">
                        @if($verificationCode)
                        <div class="qr-container">
                            <div id="{{ $qrElementId }}" style="width:78px;height:78px;"></div>
                        </div>
                        <div class="qr-label">امسح للتحقق</div>
                        @endif
                    </div>
                    <div class="signature-block">
                        <div class="signature-name">{{ $academySignatureName }}</div>
                        <div class="signature-label">{{ $academySignatureTitle }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="bottom-strip"></div>
    </div>
</div>

@if($verificationCode)
@once
@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
@endpush
@endonce
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var el = document.getElementById(@json($qrElementId));
    if (el && typeof QRCode !== 'undefined' && !el.hasChildNodes()) {
        new QRCode(el, {
            text: @json($verificationUrl),
            width: 78,
            height: 78,
            colorDark: '#1B2C6E',
            colorLight: '#FFFFFF',
            correctLevel: QRCode.CorrectLevel.M
        });
    }
});
</script>
@endpush
@endif
