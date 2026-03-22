<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('hiring_academies')) {
            Schema::create('hiring_academies', function (Blueprint $table) {
                $table->id();
                $table->string('name', 180);
                $table->string('slug', 190)->unique();
                $table->string('legal_name', 255)->nullable();
                $table->string('city', 120)->nullable();
                $table->string('address', 500)->nullable();
                $table->string('contact_name', 120)->nullable();
                $table->string('contact_email', 190)->nullable();
                $table->string('contact_phone', 50)->nullable();
                $table->string('website', 255)->nullable();
                $table->string('tax_id', 80)->nullable();
                $table->enum('status', ['active', 'suspended', 'lead'])->default('active')->index();
                $table->text('commercial_notes')->nullable()->comment('شروط تعاقد / تسعير داخلي');
                $table->text('internal_notes')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        if (Schema::hasTable('academy_opportunities') && ! Schema::hasColumn('academy_opportunities', 'hiring_academy_id')) {
            Schema::table('academy_opportunities', function (Blueprint $table) {
                $table->foreignId('hiring_academy_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('hiring_academies')
                    ->nullOnDelete();
            });
        }

        if (! Schema::hasTable('recruitment_teacher_presentations')) {
            Schema::create('recruitment_teacher_presentations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('academy_opportunity_id')->constrained('academy_opportunities')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('display_code', 32)->unique();
                $table->enum('status', [
                    'draft',
                    'shared_with_academy',
                    'academy_interested',
                    'academy_declined',
                    'hired',
                    'withdrawn',
                ])->default('draft')->index();
                $table->boolean('hide_identity')->default(false)->comment('إخفاء الاسم عن الأكاديمية واستخدام المرجع فقط');
                $table->text('curated_public_profile')->nullable()->comment('الملف المعتمد للعرض على الأكاديمية');
                $table->text('internal_notes')->nullable();
                $table->text('academy_feedback')->nullable()->comment('رد الأكاديمية كما يسجله فريق المنصة');
                $table->timestamp('shared_with_academy_at')->nullable();
                $table->timestamp('academy_responded_at')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->unique(['academy_opportunity_id', 'user_id'], 'rtp_opportunity_user_unique');
                $table->index(['academy_opportunity_id', 'status'], 'rtp_opp_status_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('recruitment_teacher_presentations');
        if (Schema::hasTable('academy_opportunities') && Schema::hasColumn('academy_opportunities', 'hiring_academy_id')) {
            Schema::table('academy_opportunities', function (Blueprint $table) {
                $table->dropConstrainedForeignId('hiring_academy_id');
            });
        }
        Schema::dropIfExists('hiring_academies');
    }
};
