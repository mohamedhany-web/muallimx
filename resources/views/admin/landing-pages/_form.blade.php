{{-- محرر أقسام صفحة الهبوط — يتوقع: $sectionsJson (array), $landingPage (optional) --}}
@php
    $initialSections = $sectionsJson ?? [];
@endphp

<div class="space-y-6"
     x-data="landingPageEditor(@js($initialSections))"
     x-init="syncJson()">

    <input type="hidden" name="sections_json" :value="sectionsJson">

    {{-- بيانات أساسية --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-6 space-y-4">
        <h2 class="text-lg font-bold text-slate-900">بيانات الصفحة</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="md:col-span-2">
                <label class="block text-sm font-semibold text-slate-700 mb-1">عنوان الصفحة (داخلي) *</label>
                <input type="text" name="title" value="{{ old('title', $landingPage->title ?? '') }}" required
                       class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm" placeholder="مثال: إعلان فيسبوك — باقة المعلمين">
                @error('title')<p class="text-rose-600 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">الرابط المختصر (slug)</label>
                <div class="flex items-center gap-2" dir="ltr">
                    <span class="text-xs text-slate-500 whitespace-nowrap">/lp/</span>
                    <input type="text" name="slug" value="{{ old('slug', $landingPage->slug ?? '') }}"
                           class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm" placeholder="facebook-teachers" pattern="[a-z0-9]+(?:-[a-z0-9]+)*">
                </div>
                @error('slug')<p class="text-rose-600 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="flex items-center gap-3 pt-6">
                <label class="inline-flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" class="rounded border-slate-300 text-sky-600"
                           @checked(old('is_active', $landingPage->is_active ?? true))>
                    <span class="text-sm font-semibold text-slate-700">نشطة / منشورة</span>
                </label>
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">عنوان ظاهر (اختياري)</label>
                <input type="text" name="headline" value="{{ old('headline', $landingPage->headline ?? '') }}"
                       class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm">
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">وصف مختصر (اختياري)</label>
                <input type="text" name="subheadline" value="{{ old('subheadline', $landingPage->subheadline ?? '') }}"
                       class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm">
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">بداية النشر</label>
                <input type="datetime-local" name="starts_at"
                       value="{{ old('starts_at', isset($landingPage) && $landingPage->starts_at ? $landingPage->starts_at->format('Y-m-d\TH:i') : '') }}"
                       class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm">
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">نهاية النشر</label>
                <input type="datetime-local" name="ends_at"
                       value="{{ old('ends_at', isset($landingPage) && $landingPage->ends_at ? $landingPage->ends_at->format('Y-m-d\TH:i') : '') }}"
                       class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm">
            </div>
        </div>
    </div>

    {{-- SEO + UTM --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-6 space-y-4">
        <h2 class="text-lg font-bold text-slate-900">SEO والإعلان</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Meta Title</label>
                <input type="text" name="meta_title" value="{{ old('meta_title', $landingPage->meta_title ?? '') }}"
                       class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm">
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">UTM Source</label>
                <input type="text" name="utm_source" value="{{ old('utm_source', $landingPage->utm_source ?? '') }}"
                       class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm" placeholder="facebook">
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-semibold text-slate-700 mb-1">Meta Description</label>
                <textarea name="meta_description" rows="2" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm">{{ old('meta_description', $landingPage->meta_description ?? '') }}</textarea>
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">UTM Campaign</label>
                <input type="text" name="utm_campaign" value="{{ old('utm_campaign', $landingPage->utm_campaign ?? '') }}"
                       class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm" placeholder="teachers-q3">
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">صورة مشاركة (OG)</label>
                <input type="file" name="og_image" accept="image/*" class="w-full text-sm">
                @if(!empty($landingPage?->og_image_path))
                    <div class="mt-2 flex items-center gap-3">
                        <img src="{{ $landingPage->ogImageUrl() }}" alt="" class="h-14 rounded-lg border border-slate-200">
                        <label class="inline-flex items-center gap-2 text-xs text-rose-600">
                            <input type="checkbox" name="remove_og_image" value="1"> حذف الصورة
                        </label>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- الأقسام --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-6 space-y-4">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-bold text-slate-900">أقسام الصفحة</h2>
                <p class="text-sm text-slate-500 mt-0.5">أضف رتّب وعدّل الأقسام: بطل، نص، فيديو يوتيوب، مزايا، شهادات، دعوة للإجراء.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <template x-for="opt in sectionOptions" :key="opt.type">
                    <button type="button" @click="addSection(opt.type)"
                            class="px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-sky-50 text-slate-700 hover:text-sky-700 text-xs font-bold border border-slate-200">
                        <i class="fas fa-plus ml-1"></i>
                        <span x-text="opt.label"></span>
                    </button>
                </template>
            </div>
        </div>

        @error('sections_json')
            <div class="p-3 bg-rose-50 border border-rose-200 rounded-xl text-rose-700 text-sm">{{ $message }}</div>
        @enderror

        <div class="space-y-4" x-show="sections.length > 0">
            <template x-for="(section, index) in sections" :key="section._key">
                <div class="border border-slate-200 rounded-2xl overflow-hidden bg-slate-50/50">
                    <div class="flex flex-wrap items-center justify-between gap-2 px-4 py-3 bg-slate-100 border-b border-slate-200">
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-bold text-slate-500" x-text="'#' + (index + 1)"></span>
                            <span class="text-sm font-bold text-slate-800" x-text="typeLabel(section.type)"></span>
                        </div>
                        <div class="flex items-center gap-1">
                            <button type="button" @click="moveSection(index, -1)" class="w-8 h-8 rounded-lg bg-white border border-slate-200 text-slate-600 hover:bg-slate-50" title="أعلى"><i class="fas fa-arrow-up text-xs"></i></button>
                            <button type="button" @click="moveSection(index, 1)" class="w-8 h-8 rounded-lg bg-white border border-slate-200 text-slate-600 hover:bg-slate-50" title="أسفل"><i class="fas fa-arrow-down text-xs"></i></button>
                            <button type="button" @click="removeSection(index)" class="w-8 h-8 rounded-lg bg-rose-50 border border-rose-100 text-rose-600 hover:bg-rose-100" title="حذف"><i class="fas fa-trash text-xs"></i></button>
                        </div>
                    </div>
                    <div class="p-4 space-y-3">
                        {{-- Hero --}}
                        <template x-if="section.type === 'hero'">
                            <div class="space-y-3">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-600 mb-1">العنوان</label>
                                    <input type="text" x-model="section.headline" @input="syncJson()" class="w-full px-3 py-2 border border-slate-200 rounded-xl text-sm bg-white">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-600 mb-1">النص</label>
                                    <textarea x-model="section.text" @input="syncJson()" rows="3" class="w-full px-3 py-2 border border-slate-200 rounded-xl text-sm bg-white"></textarea>
                                </div>
                                <div>
                                    <div class="flex items-center justify-between mb-2">
                                        <label class="text-xs font-semibold text-slate-600">الأزرار</label>
                                        <button type="button" @click="addButton(section)" class="text-xs font-bold text-sky-600">+ زر</button>
                                    </div>
                                    <template x-for="(btn, bi) in section.buttons" :key="bi">
                                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2 mb-2 p-3 bg-white rounded-xl border border-slate-100">
                                            <input type="text" x-model="btn.label" @input="syncJson()" placeholder="نص الزر" class="px-3 py-2 border border-slate-200 rounded-lg text-sm">
                                            <select x-model="btn.action" @change="syncJson()" class="px-3 py-2 border border-slate-200 rounded-lg text-sm">
                                                <option value="register">تسجيل</option>
                                                <option value="pricing">الباقات</option>
                                                <option value="whatsapp">واتساب</option>
                                                <option value="custom">رابط مخصص</option>
                                            </select>
                                            <template x-if="btn.action === 'whatsapp'">
                                                <input type="text" x-model="btn.whatsapp_number" @input="syncJson()" placeholder="رقم واتساب (2010...)" class="px-3 py-2 border border-slate-200 rounded-lg text-sm" dir="ltr">
                                            </template>
                                            <template x-if="btn.action === 'whatsapp'">
                                                <input type="text" x-model="btn.whatsapp_message" @input="syncJson()" placeholder="رسالة واتساب" class="px-3 py-2 border border-slate-200 rounded-lg text-sm sm:col-span-2">
                                            </template>
                                            <template x-if="btn.action === 'custom'">
                                                <input type="url" x-model="btn.url" @input="syncJson()" placeholder="https://..." class="px-3 py-2 border border-slate-200 rounded-lg text-sm sm:col-span-2" dir="ltr">
                                            </template>
                                            <button type="button" @click="section.buttons.splice(bi,1); syncJson()" class="text-xs text-rose-600 font-bold">حذف الزر</button>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>

                        {{-- Text --}}
                        <template x-if="section.type === 'text'">
                            <div class="space-y-3">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-600 mb-1">العنوان</label>
                                    <input type="text" x-model="section.title" @input="syncJson()" class="w-full px-3 py-2 border border-slate-200 rounded-xl text-sm bg-white">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-600 mb-1">المحتوى</label>
                                    <textarea x-model="section.body" @input="syncJson()" rows="5" class="w-full px-3 py-2 border border-slate-200 rounded-xl text-sm bg-white"></textarea>
                                </div>
                            </div>
                        </template>

                        {{-- Video --}}
                        <template x-if="section.type === 'video'">
                            <div class="space-y-3">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-600 mb-1">عنوان الفيديو</label>
                                    <input type="text" x-model="section.title" @input="syncJson()" class="w-full px-3 py-2 border border-slate-200 rounded-xl text-sm bg-white">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-600 mb-1">وصف مختصر</label>
                                    <input type="text" x-model="section.description" @input="syncJson()" class="w-full px-3 py-2 border border-slate-200 rounded-xl text-sm bg-white">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-600 mb-1">رابط يوتيوب *</label>
                                    <input type="text" x-model="section.youtube_url" @input="syncJson()" dir="ltr"
                                           placeholder="https://www.youtube.com/watch?v=... أو Shorts"
                                           class="w-full px-3 py-2 border border-slate-200 rounded-xl text-sm bg-white">
                                    <p class="text-[11px] text-slate-500 mt-1">يقبل رابط watch أو youtu.be أو Shorts أو Live</p>
                                </div>
                            </div>
                        </template>

                        {{-- Features --}}
                        <template x-if="section.type === 'features'">
                            <div class="space-y-3">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-600 mb-1">عنوان القسم</label>
                                    <input type="text" x-model="section.title" @input="syncJson()" class="w-full px-3 py-2 border border-slate-200 rounded-xl text-sm bg-white">
                                </div>
                                <div class="flex items-center justify-between">
                                    <label class="text-xs font-semibold text-slate-600">المزايا</label>
                                    <button type="button" @click="addFeature(section)" class="text-xs font-bold text-sky-600">+ ميزة</button>
                                </div>
                                <template x-for="(feat, fi) in section.items" :key="fi">
                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 p-3 bg-white rounded-xl border border-slate-100 mb-2">
                                        <input type="text" x-model="feat.icon" @input="syncJson()" placeholder="fa-check" class="px-3 py-2 border border-slate-200 rounded-lg text-sm" dir="ltr">
                                        <input type="text" x-model="feat.title" @input="syncJson()" placeholder="العنوان" class="px-3 py-2 border border-slate-200 rounded-lg text-sm">
                                        <input type="text" x-model="feat.description" @input="syncJson()" placeholder="الوصف" class="px-3 py-2 border border-slate-200 rounded-lg text-sm">
                                        <button type="button" @click="section.items.splice(fi,1); syncJson()" class="text-xs text-rose-600 font-bold sm:col-span-3 text-start">حذف الميزة</button>
                                    </div>
                                </template>
                            </div>
                        </template>

                        {{-- Testimonials --}}
                        <template x-if="section.type === 'testimonials'">
                            <div class="space-y-3">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-600 mb-1">عنوان القسم</label>
                                    <input type="text" x-model="section.title" @input="syncJson()" class="w-full px-3 py-2 border border-slate-200 rounded-xl text-sm bg-white">
                                </div>
                                <div class="flex items-center justify-between">
                                    <label class="text-xs font-semibold text-slate-600">الشهادات</label>
                                    <button type="button" @click="addTestimonial(section)" class="text-xs font-bold text-sky-600">+ شهادة</button>
                                </div>
                                <template x-for="(t, ti) in section.items" :key="ti">
                                    <div class="space-y-2 p-3 bg-white rounded-xl border border-slate-100 mb-2">
                                        <input type="text" x-model="t.name" @input="syncJson()" placeholder="الاسم" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm">
                                        <input type="text" x-model="t.role" @input="syncJson()" placeholder="الصفة (معلم رياضيات...)" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm">
                                        <textarea x-model="t.quote" @input="syncJson()" rows="2" placeholder="نص الشهادة" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm"></textarea>
                                        <button type="button" @click="section.items.splice(ti,1); syncJson()" class="text-xs text-rose-600 font-bold">حذف</button>
                                    </div>
                                </template>
                            </div>
                        </template>

                        {{-- CTA --}}
                        <template x-if="section.type === 'cta'">
                            <div class="space-y-3">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-600 mb-1">العنوان</label>
                                    <input type="text" x-model="section.title" @input="syncJson()" class="w-full px-3 py-2 border border-slate-200 rounded-xl text-sm bg-white">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-600 mb-1">النص</label>
                                    <textarea x-model="section.text" @input="syncJson()" rows="2" class="w-full px-3 py-2 border border-slate-200 rounded-xl text-sm bg-white"></textarea>
                                </div>
                                <div>
                                    <div class="flex items-center justify-between mb-2">
                                        <label class="text-xs font-semibold text-slate-600">الأزرار (تسجيل / باقات / واتساب / مخصص)</label>
                                        <button type="button" @click="addButton(section)" class="text-xs font-bold text-sky-600">+ زر</button>
                                    </div>
                                    <template x-for="(btn, bi) in section.buttons" :key="bi">
                                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2 mb-2 p-3 bg-white rounded-xl border border-slate-100">
                                            <input type="text" x-model="btn.label" @input="syncJson()" placeholder="نص الزر" class="px-3 py-2 border border-slate-200 rounded-lg text-sm">
                                            <select x-model="btn.action" @change="syncJson()" class="px-3 py-2 border border-slate-200 rounded-lg text-sm">
                                                <option value="register">تسجيل</option>
                                                <option value="pricing">الباقات</option>
                                                <option value="whatsapp">واتساب</option>
                                                <option value="custom">رابط مخصص</option>
                                            </select>
                                            <template x-if="btn.action === 'whatsapp'">
                                                <input type="text" x-model="btn.whatsapp_number" @input="syncJson()" placeholder="رقم واتساب" class="px-3 py-2 border border-slate-200 rounded-lg text-sm" dir="ltr">
                                            </template>
                                            <template x-if="btn.action === 'whatsapp'">
                                                <input type="text" x-model="btn.whatsapp_message" @input="syncJson()" placeholder="رسالة" class="px-3 py-2 border border-slate-200 rounded-lg text-sm sm:col-span-2">
                                            </template>
                                            <template x-if="btn.action === 'custom'">
                                                <input type="url" x-model="btn.url" @input="syncJson()" placeholder="https://..." class="px-3 py-2 border border-slate-200 rounded-lg text-sm sm:col-span-2" dir="ltr">
                                            </template>
                                            <button type="button" @click="section.buttons.splice(bi,1); syncJson()" class="text-xs text-rose-600 font-bold">حذف الزر</button>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </template>
        </div>

        <div x-show="sections.length === 0" class="text-center py-10 text-slate-500 text-sm border border-dashed border-slate-200 rounded-2xl">
            لا توجد أقسام بعد — اختر نوع قسم من الأزرار أعلاه أو استخدم «من قالب إعلان».
        </div>
    </div>
</div>

@once
@push('scripts')
<script>
function landingPageEditor(initial) {
    const withKeys = (arr) => (Array.isArray(arr) ? arr : []).map((s, i) => ({
        ...JSON.parse(JSON.stringify(s)),
        _key: 's' + Date.now() + '_' + i + '_' + Math.random().toString(36).slice(2, 7),
        buttons: Array.isArray(s.buttons) ? s.buttons : (s.type === 'hero' || s.type === 'cta' ? [] : undefined),
        items: Array.isArray(s.items) ? s.items : (s.type === 'features' || s.type === 'testimonials' ? [] : undefined),
    }));

    return {
        sections: withKeys(initial),
        sectionsJson: '[]',
        sectionOptions: [
            { type: 'hero', label: 'بطل (Hero)' },
            { type: 'text', label: 'نص' },
            { type: 'video', label: 'فيديو يوتيوب' },
            { type: 'features', label: 'مزايا' },
            { type: 'testimonials', label: 'شهادات' },
            { type: 'cta', label: 'دعوة للإجراء' },
        ],
        typeLabel(type) {
            const map = {
                hero: 'بطل (Hero)',
                text: 'نص',
                video: 'فيديو يوتيوب',
                features: 'مزايا',
                testimonials: 'شهادات',
                cta: 'دعوة للإجراء',
            };
            return map[type] || type;
        },
        blankSection(type) {
            const base = { type, sort: this.sections.length, _key: 's' + Date.now() + '_' + Math.random().toString(36).slice(2, 7) };
            if (type === 'hero') return { ...base, headline: '', text: '', buttons: [{ label: 'ابدأ مجاناً', action: 'register' }] };
            if (type === 'text') return { ...base, title: '', body: '' };
            if (type === 'video') return { ...base, title: '', description: '', youtube_url: '', youtube_id: null };
            if (type === 'features') return { ...base, title: 'ماذا نقدّم؟', items: [{ icon: 'fa-check', title: '', description: '' }] };
            if (type === 'testimonials') return { ...base, title: 'ماذا يقول المعلمون؟', items: [{ name: '', role: '', quote: '' }] };
            if (type === 'cta') return { ...base, title: '', text: '', buttons: [
                { label: 'إنشاء حساب', action: 'register' },
                { label: 'واتساب', action: 'whatsapp', whatsapp_number: '', whatsapp_message: '' },
            ]};
            return base;
        },
        addSection(type) {
            this.sections.push(this.blankSection(type));
            this.syncJson();
        },
        removeSection(index) {
            this.sections.splice(index, 1);
            this.syncJson();
        },
        moveSection(index, dir) {
            const next = index + dir;
            if (next < 0 || next >= this.sections.length) return;
            const tmp = this.sections[index];
            this.sections[index] = this.sections[next];
            this.sections[next] = tmp;
            this.syncJson();
        },
        addButton(section) {
            if (!Array.isArray(section.buttons)) section.buttons = [];
            section.buttons.push({ label: 'زر جديد', action: 'register', whatsapp_number: '', whatsapp_message: '', url: '' });
            this.syncJson();
        },
        addFeature(section) {
            if (!Array.isArray(section.items)) section.items = [];
            section.items.push({ icon: 'fa-check', title: '', description: '' });
            this.syncJson();
        },
        addTestimonial(section) {
            if (!Array.isArray(section.items)) section.items = [];
            section.items.push({ name: '', role: '', quote: '' });
            this.syncJson();
        },
        syncJson() {
            const clean = this.sections.map((s, i) => {
                const { _key, ...rest } = s;
                rest.sort = i;
                return rest;
            });
            this.sectionsJson = JSON.stringify(clean);
        },
    };
}
</script>
@endpush
@endonce
