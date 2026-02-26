<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('community_models', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            /** شرح تفصيلي من المساهم: كل الخطوات التي مر بها (معالجة بيانات، تدريب، تقييم، إلخ) */
            $table->longText('methodology_steps')->nullable();
            /** ربط النموذج بمجموعة بيانات من المجتمع */
            $table->foreignId('community_dataset_id')->nullable()->constrained('community_datasets')->nullOnDelete();
            /** مقاييس الأداء (JSON) مثل: {"accuracy": 0.95, "f1": 0.92, "loss": 0.1} */
            $table->json('performance_metrics')->nullable();
            /** ترخيص الاستخدام (مثلاً: MIT, Apache-2.0, CC-BY, استخدام شخصي فقط) */
            $table->string('license', 100)->nullable();
            /** طريقة الاستخدام أو الاستدعاء (كود نموذجي أو تعليمات) */
            $table->text('usage_instructions')->nullable();
            /** مسار ملف واحد (للتوافق) أو قائمة ملفات في حقل files */
            $table->string('file_path')->nullable();
            $table->string('file_size')->nullable();
            $table->json('files')->nullable()->comment('قائمة الملفات: [{path, original_name, size}] — تُخزَّن على Cloudflare R2');
            $table->unsignedInteger('downloads_count')->default(0);
            $table->string('status', 20)->default('pending'); // pending, approved, rejected
            $table->boolean('is_active')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('community_models');
    }
};
