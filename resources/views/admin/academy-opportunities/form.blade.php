<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
        <label class="block text-xs font-semibold text-slate-600 mb-1">اسم الجهة / الأكاديمية</label>
        <input type="text" name="organization_name" value="{{ old('organization_name', $opportunity->organization_name ?? '') }}" class="w-full px-3 py-2 rounded-lg border border-slate-200">
        @error('organization_name')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-xs font-semibold text-slate-600 mb-1">عنوان الفرصة</label>
        <input type="text" name="title" value="{{ old('title', $opportunity->title ?? '') }}" class="w-full px-3 py-2 rounded-lg border border-slate-200">
        @error('title')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-xs font-semibold text-slate-600 mb-1">التخصص</label>
        <input type="text" name="specialization" value="{{ old('specialization', $opportunity->specialization ?? '') }}" class="w-full px-3 py-2 rounded-lg border border-slate-200">
    </div>
    <div>
        <label class="block text-xs font-semibold text-slate-600 mb-1">المدينة</label>
        <input type="text" name="city" value="{{ old('city', $opportunity->city ?? '') }}" class="w-full px-3 py-2 rounded-lg border border-slate-200">
    </div>
    <div>
        <label class="block text-xs font-semibold text-slate-600 mb-1">نمط العمل</label>
        <select name="work_mode" class="w-full px-3 py-2 rounded-lg border border-slate-200">
            @foreach(['remote' => 'عن بُعد', 'onsite' => 'حضوري', 'hybrid' => 'هجين'] as $k => $label)
                <option value="{{ $k }}" {{ old('work_mode', $opportunity->work_mode ?? 'remote') === $k ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-xs font-semibold text-slate-600 mb-1">الحالة</label>
        <select name="status" class="w-full px-3 py-2 rounded-lg border border-slate-200">
            @foreach(['active', 'paused', 'closed'] as $k)
                <option value="{{ $k }}" {{ old('status', $opportunity->status ?? 'active') === $k ? 'selected' : '' }}>{{ $k }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-xs font-semibold text-slate-600 mb-1">آخر موعد للتقديم</label>
        <input type="date" name="apply_until" value="{{ old('apply_until', optional($opportunity->apply_until ?? null)->format('Y-m-d')) }}" class="w-full px-3 py-2 rounded-lg border border-slate-200">
    </div>
    <div class="flex items-center gap-2 pt-6">
        <input type="hidden" name="is_featured" value="0">
        <input type="checkbox" name="is_featured" value="1" {{ (int) old('is_featured', $opportunity->is_featured ?? 0) === 1 ? 'checked' : '' }}>
        <span class="text-sm text-slate-700">فرصة مميزة</span>
    </div>
</div>
<div>
    <label class="block text-xs font-semibold text-slate-600 mb-1">المتطلبات</label>
    <textarea name="requirements" rows="6" class="w-full px-3 py-2 rounded-lg border border-slate-200">{{ old('requirements', $opportunity->requirements ?? '') }}</textarea>
</div>
