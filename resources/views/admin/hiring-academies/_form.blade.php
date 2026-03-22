@php
    $academy = $academy ?? null;
@endphp
<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div class="md:col-span-2">
        <label class="block text-xs font-semibold text-slate-600 mb-1">اسم الأكاديمية <span class="text-rose-500">*</span></label>
        <input type="text" name="name" value="{{ old('name', $academy->name ?? '') }}" required class="w-full px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-800 dark:text-white">
        @error('name')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
    </div>
    <div class="md:col-span-2">
        <label class="block text-xs font-semibold text-slate-600 mb-1">الاسم القانوني / السجل التجاري (اختياري)</label>
        <input type="text" name="legal_name" value="{{ old('legal_name', $academy->legal_name ?? '') }}" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-800 dark:text-white">
    </div>
    <div>
        <label class="block text-xs font-semibold text-slate-600 mb-1">المدينة</label>
        <input type="text" name="city" value="{{ old('city', $academy->city ?? '') }}" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-800 dark:text-white">
    </div>
    <div>
        <label class="block text-xs font-semibold text-slate-600 mb-1">حالة العميل</label>
        <select name="status" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-800 dark:text-white">
            @foreach(\App\Models\HiringAcademy::statusLabels() as $k => $label)
                <option value="{{ $k }}" @selected(old('status', $academy->status ?? 'active') === $k)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="md:col-span-2">
        <label class="block text-xs font-semibold text-slate-600 mb-1">العنوان</label>
        <input type="text" name="address" value="{{ old('address', $academy->address ?? '') }}" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-800 dark:text-white">
    </div>
    <div>
        <label class="block text-xs font-semibold text-slate-600 mb-1">اسم جهة الاتصال</label>
        <input type="text" name="contact_name" value="{{ old('contact_name', $academy->contact_name ?? '') }}" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-800 dark:text-white">
    </div>
    <div>
        <label class="block text-xs font-semibold text-slate-600 mb-1">البريد</label>
        <input type="email" name="contact_email" value="{{ old('contact_email', $academy->contact_email ?? '') }}" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-800 dark:text-white">
    </div>
    <div>
        <label class="block text-xs font-semibold text-slate-600 mb-1">الهاتف</label>
        <input type="text" name="contact_phone" value="{{ old('contact_phone', $academy->contact_phone ?? '') }}" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-800 dark:text-white">
    </div>
    <div>
        <label class="block text-xs font-semibold text-slate-600 mb-1">الموقع</label>
        <input type="text" name="website" value="{{ old('website', $academy->website ?? '') }}" placeholder="https://" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-800 dark:text-white">
    </div>
    <div>
        <label class="block text-xs font-semibold text-slate-600 mb-1">الرقم الضريبي / تعريف</label>
        <input type="text" name="tax_id" value="{{ old('tax_id', $academy->tax_id ?? '') }}" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-800 dark:text-white">
    </div>
    <div class="md:col-span-2">
        <label class="block text-xs font-semibold text-slate-600 mb-1">ملاحظات تجارية / تعاقد (داخلية)</label>
        <textarea name="commercial_notes" rows="3" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-800 dark:text-white">{{ old('commercial_notes', $academy->commercial_notes ?? '') }}</textarea>
    </div>
    <div class="md:col-span-2">
        <label class="block text-xs font-semibold text-slate-600 mb-1">ملاحظات داخلية للفريق</label>
        <textarea name="internal_notes" rows="3" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-800 dark:text-white">{{ old('internal_notes', $academy->internal_notes ?? '') }}</textarea>
    </div>
</div>
