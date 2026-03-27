<?php

namespace App\Console\Commands;

use App\Models\AgreementPayment;
use App\Models\ConsultationRequest;
use App\Models\InstructorAgreement;
use Illuminate\Console\Command;

class BackfillConsultationPayments extends Command
{
    protected $signature = 'consultations:backfill-payments
                            {--dry-run : استعراض النتائج بدون إنشاء مدفوعات}
                            {--instructor_id= : ترحيل لمدرب واحد فقط}';

    protected $description = 'إنشاء مستحقات الاستشارات المكتملة القديمة بأثر رجعي داخل حسابات المدربين.';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $instructorId = $this->option('instructor_id');

        $query = ConsultationRequest::query()
            ->where('status', ConsultationRequest::STATUS_COMPLETED)
            ->with(['student'])
            ->orderBy('id');

        if (! empty($instructorId)) {
            $query->where('instructor_id', (int) $instructorId);
        }

        $total = (clone $query)->count();
        $this->info('عدد الاستشارات المكتملة المرشحة للفحص: ' . $total);

        if ($total === 0) {
            $this->info('لا توجد استشارات مكتملة للترحيل.');
            return self::SUCCESS;
        }

        $created = 0;
        $skippedHasPayment = 0;
        $skippedNoAgreement = 0;
        $skippedZeroRate = 0;

        $query->chunkById(200, function ($consultations) use (
            &$created,
            &$skippedHasPayment,
            &$skippedNoAgreement,
            &$skippedZeroRate,
            $dryRun
        ) {
            foreach ($consultations as $consultation) {
                $hasExisting = AgreementPayment::query()
                    ->where('instructor_id', $consultation->instructor_id)
                    ->where('type', AgreementPayment::TYPE_CONSULTATION_SESSION)
                    ->where('description', 'like', '%#' . $consultation->id . '%')
                    ->exists();

                if ($hasExisting) {
                    $skippedHasPayment++;
                    continue;
                }

                $agreement = InstructorAgreement::query()
                    ->where('instructor_id', $consultation->instructor_id)
                    ->whereIn('status', [
                        InstructorAgreement::STATUS_ACTIVE,
                        InstructorAgreement::STATUS_SUSPENDED,
                        InstructorAgreement::STATUS_TERMINATED,
                        InstructorAgreement::STATUS_COMPLETED,
                    ])
                    ->where(function ($q) use ($consultation) {
                        $q->whereNull('start_date')
                            ->orWhereDate('start_date', '<=', $consultation->scheduled_at ?? $consultation->created_at);
                    })
                    ->where(function ($q) use ($consultation) {
                        $q->whereNull('end_date')
                            ->orWhereDate('end_date', '>=', $consultation->scheduled_at ?? $consultation->created_at);
                    })
                    ->where(function ($q) {
                        $q->where('type', 'consultation_session')
                            ->orWhere('billing_type', InstructorAgreement::BILLING_CONSULTATION);
                    })
                    ->latest('start_date')
                    ->latest('id')
                    ->first();

                if (! $agreement) {
                    $skippedNoAgreement++;
                    continue;
                }

                $amount = (float) ($agreement->rate ?? 0);
                if ($amount <= 0) {
                    $skippedZeroRate++;
                    continue;
                }

                if ($dryRun) {
                    $created++;
                    continue;
                }

                AgreementPayment::create([
                    'agreement_id' => $agreement->id,
                    'instructor_id' => $consultation->instructor_id,
                    'type' => AgreementPayment::TYPE_CONSULTATION_SESSION,
                    'amount' => $amount,
                    'status' => AgreementPayment::STATUS_APPROVED,
                    'description' => 'مستحق استشارة مكتملة #' . $consultation->id . ' — الطالب: ' . ($consultation->student->name ?? '—'),
                    'payment_date' => now(),
                    'created_by' => auth()->id(),
                ]);

                $created++;
            }
        });

        $this->newLine();
        $this->line($dryRun ? 'نتيجة المعاينة (Dry Run):' : 'نتيجة الترحيل:');
        $this->line('- سيتم/تم إنشاء مدفوعات: ' . $created);
        $this->line('- تم تخطيها (لها مدفوعة موجودة): ' . $skippedHasPayment);
        $this->line('- تم تخطيها (لا توجد اتفاقية استشارات مطابقة): ' . $skippedNoAgreement);
        $this->line('- تم تخطيها (سعر الاتفاقية = 0): ' . $skippedZeroRate);

        return self::SUCCESS;
    }
}

