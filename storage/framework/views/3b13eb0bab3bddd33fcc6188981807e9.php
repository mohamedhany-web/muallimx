
<?php
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
?>

<?php echo $__env->make('components.certificate-enhanced-styles', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<div class="mx-cert-enhanced certificate-print" id="certificate-enhanced">
    <div class="certificate">
        <?php if($showPreviewWatermark): ?>
            <div class="preview-watermark"><span>معاينة</span></div>
        <?php endif; ?>

        <div class="top-band">
            <div class="brand-logo">
                <div class="logo-icon">
                    <img src="<?php echo e($logoUrl); ?>" alt="<?php echo e($brandSlug); ?>">
                </div>
                <div>
                    <div class="brand-name"><?php echo e($brandSlug); ?></div>
                    <div class="brand-sub">منصة التعلّم الذكي والشهادات الرقمية</div>
                </div>
            </div>
            <div style="text-align:left; position:relative; z-index:1;">
                <div class="cert-type-label">Certificate of Completion</div>
                <?php if($certificateNumber): ?>
                    <div class="cert-number"><?php echo e($certificateNumber); ?></div>
                <?php endif; ?>
            </div>
            <div class="top-band-line"></div>
        </div>

        <div class="cert-body">
            <img src="<?php echo e($watermarkUrl); ?>" alt="" class="cert-body-watermark" aria-hidden="true">

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
                <div class="recipient-name"><?php echo e($studentName); ?></div>
                <div class="recipient-underline"></div>
            </div>

            <div class="info-section">
                <div class="completion-text">قد أتمَّ بنجاح متطلبات</div>
                <div class="course-title"><?php echo e($courseName); ?></div>
                <div class="completion-text" style="margin-top:8px;">
                    المُقدَّم عبر منصة <span><?php echo e($brandSlug); ?></span>
                </div>
            </div>

            <div class="details-row">
                <div class="details-row-inner">
                    <?php if($issueDate): ?>
                    <div class="detail-item">
                        <span class="detail-label">تاريخ الإصدار</span>
                        <span class="detail-value"><?php echo e($issueDate); ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="detail-item">
                        <span class="detail-label">المدرب</span>
                        <span class="detail-value"><?php echo e($instructorSignatureName); ?></span>
                    </div>
                    <?php if($courseDuration): ?>
                    <div class="detail-item">
                        <span class="detail-label">مدة الدورة</span>
                        <span class="detail-value"><?php echo e($courseDuration); ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if($verificationCode): ?>
                    <div class="detail-item">
                        <span class="detail-label">رمز التحقق</span>
                        <span class="detail-value" style="font-size:10px; font-family:monospace;"><?php echo e($verificationCode); ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="cert-footer">
                <div class="cert-footer-row">
                    <div class="signature-block">
                        <div class="signature-name"><?php echo e($instructorSignatureName); ?></div>
                        <div class="signature-label"><?php echo e($instructorSignatureTitle); ?></div>
                    </div>
                    <div class="seal">
                        <div class="seal-circle">
                            <div class="seal-text-main"><?php echo e($brandSlug); ?><br>CERTIFIED</div>
                            <div class="seal-star">★</div>
                            <div class="seal-year"><?php echo e($issueYear); ?></div>
                        </div>
                    </div>
                    <div class="qr-block">
                        <?php if($verificationCode): ?>
                        <div class="qr-container">
                            <div id="<?php echo e($qrElementId); ?>" style="width:78px;height:78px;"></div>
                        </div>
                        <div class="qr-label">امسح للتحقق</div>
                        <?php endif; ?>
                    </div>
                    <div class="signature-block">
                        <div class="signature-name"><?php echo e($academySignatureName); ?></div>
                        <div class="signature-label"><?php echo e($academySignatureTitle); ?></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="bottom-strip"></div>
    </div>
</div>

<?php if($verificationCode): ?>
<?php if (! $__env->hasRenderedOnce('f039b619-8843-40ca-8e97-4b17d04fe556')): $__env->markAsRenderedOnce('f039b619-8843-40ca-8e97-4b17d04fe556'); ?>
<?php $__env->startPush('scripts'); ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<?php $__env->stopPush(); ?>
<?php endif; ?>
<?php $__env->startPush('scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var el = document.getElementById(<?php echo json_encode($qrElementId, 15, 512) ?>);
    if (el && typeof QRCode !== 'undefined' && !el.hasChildNodes()) {
        new QRCode(el, {
            text: <?php echo json_encode($verificationUrl, 15, 512) ?>,
            width: 78,
            height: 78,
            colorDark: '#1B2C6E',
            colorLight: '#FFFFFF',
            correctLevel: QRCode.CorrectLevel.M
        });
    }
});
</script>
<?php $__env->stopPush(); ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\Muallimx\resources\views\components\certificate-enhanced.blade.php ENDPATH**/ ?>