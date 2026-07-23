@php
    $weekdayLabels = ['الأحد', 'الإثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة', 'السبت'];
@endphp
<div x-cloak>
    {{-- تفاصيل موعد شخصي --}}
    <div x-show="detailOpen" class="fixed inset-0 z-[85] flex items-center justify-center p-4 bg-black/50" @keydown.escape.window="detailOpen = false">
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-2xl w-full max-w-md border border-slate-200 dark:border-slate-700" @click.outside="detailOpen = false">
            <div class="p-5 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between gap-3">
                <h2 class="text-lg font-black text-slate-900 dark:text-slate-100">تفاصيل الموعد</h2>
                <button type="button" @click="detailOpen = false" class="w-9 h-9 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 flex items-center justify-center">&times;</button>
            </div>
            <div class="p-5 space-y-3 text-sm">
                <p class="font-bold text-slate-900 dark:text-slate-100" x-text="detail.title"></p>
                <p class="text-slate-600 dark:text-slate-400 whitespace-pre-line" x-text="detail.description"></p>
                <p class="text-xs text-violet-700 dark:text-violet-300 font-semibold" x-show="detail.schedule_type === 'temporary'">موعد مؤقت — يُحذف تلقائياً بعد انتهائه</p>
                <p class="text-xs text-violet-700 dark:text-violet-300 font-semibold" x-show="detail.schedule_type === 'fixed'">موعد ثابت</p>
            </div>
            <div class="p-5 border-t border-slate-100 dark:border-slate-700 flex flex-wrap gap-2">
                <button type="button" @click="detailOpen = false" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-800 text-sm font-semibold">إغلاق</button>
                <button type="button" @click="deleteDetail()" :disabled="deleting" class="px-4 py-2 rounded-xl bg-red-600 text-white text-sm font-semibold hover:bg-red-700 disabled:opacity-60">
                    <span x-text="deleting ? 'جاري الحذف...' : 'حذف الموعد'"></span>
                </button>
            </div>
        </div>
    </div>

    <div x-show="modalOpen" class="fixed inset-0 z-[80] flex items-center justify-center p-4 bg-black/50" @keydown.escape.window="modalOpen = false">
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto border border-slate-200 dark:border-slate-700" @click.outside="modalOpen = false">
            <div class="p-5 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-lg font-black text-slate-900 dark:text-slate-100">إضافة موعد في تقويمي</h2>
                    <p class="text-xs text-slate-500 mt-1">أدخل وقت الأسرة/العائلة — يُحوَّل تلقائياً لتوقيتك مع مراعاة التوقيت الصيفي/الشتوي</p>
                </div>
                <button type="button" @click="modalOpen = false" class="w-9 h-9 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 flex items-center justify-center">&times;</button>
            </div>

            <form class="p-5 space-y-4" @submit.prevent="submitForm">
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1">عنوان الحصة</label>
                    <input type="text" x-model="form.title" required maxlength="200" class="w-full rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-800 px-3 py-2 text-sm">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <label class="flex items-center gap-2 p-3 rounded-xl border cursor-pointer" :class="form.schedule_type === 'fixed' ? 'border-violet-500 bg-violet-50 dark:bg-violet-900/20' : 'border-slate-200 dark:border-slate-600'">
                        <input type="radio" value="fixed" x-model="form.schedule_type" @change="onTypeChange()">
                        <span class="text-sm font-bold">موعد ثابت</span>
                    </label>
                    <label class="flex items-center gap-2 p-3 rounded-xl border cursor-pointer" :class="form.schedule_type === 'temporary' ? 'border-amber-500 bg-amber-50 dark:bg-amber-900/20' : 'border-slate-200 dark:border-slate-600'">
                        <input type="radio" value="temporary" x-model="form.schedule_type" @change="onTypeChange()">
                        <span class="text-sm font-bold">موعد مؤقت</span>
                    </label>
                </div>
                <p class="text-[11px] text-slate-500 leading-relaxed" x-show="form.schedule_type === 'fixed'">ثابت: اختر أيام الشهر (مثلاً كل سبت الساعة 5) — يتكرر حسب اختيارك.</p>
                <p class="text-[11px] text-slate-500 leading-relaxed" x-show="form.schedule_type === 'temporary'">مؤقت: حصة مرة واحدة (إعادة جدولة) — تُحذف تلقائياً بعد انتهائها.</p>

                <div class="grid sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1">توقيت الأسرة / الطالب</label>
                        <input type="search" x-model="familyTzQuery" @input.debounce.200ms="filterFamilyTz()" placeholder="ابحث: مصر، New York، Paris..."
                               class="w-full mb-1.5 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-800 px-3 py-2 text-sm">
                        <select x-model="form.family_timezone" @change="refreshPreview()" class="w-full rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-800 px-3 py-2 text-sm" size="6">
                            <template x-for="opt in familyTzOptions" :key="opt.id">
                                <option :value="opt.id" x-text="opt.label" :selected="opt.id === form.family_timezone"></option>
                            </template>
                        </select>
                        <p class="text-[10px] text-slate-400 mt-1" x-text="form.family_timezone"></p>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1">توقيتك (العرض)</label>
                        <input type="search" x-model="teacherTzQuery" @input.debounce.200ms="filterTeacherTz()" placeholder="ابحث عن مدينتك أو الدولة..."
                               class="w-full mb-1.5 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-800 px-3 py-2 text-sm">
                        <select x-model="form.teacher_timezone" @change="refreshPreview()" class="w-full rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-800 px-3 py-2 text-sm" size="6">
                            <template x-for="opt in teacherTzOptions" :key="'t'+opt.id">
                                <option :value="opt.id" x-text="opt.label" :selected="opt.id === form.teacher_timezone"></option>
                            </template>
                        </select>
                        <p class="text-[10px] text-slate-400 mt-1" x-text="form.teacher_timezone"></p>
                    </div>
                </div>

                <div class="rounded-xl border border-slate-200 dark:border-slate-600 p-3 space-y-2">
                    <label class="block text-xs font-bold text-slate-600">ولاية أمريكا (اختياري) — يضبط توقيت الأسرة تلقائياً</label>
                    <select x-model="usState" @change="applyUsState()" class="w-full rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-800 px-3 py-2 text-sm">
                        <option value="">— اختر ولاية إن كانت الأسرة في أمريكا —</option>
                        <template x-for="(st, code) in usStates" :key="code">
                            <option :value="code" x-text="st.name + ' → ' + st.timezone"></option>
                        </template>
                    </select>
                </div>

                <div class="grid sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1">الوقت (توقيت الأسرة)</label>
                        <input type="time" x-model="form.family_time" required @change="refreshPreview()" class="w-full rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-800 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1">المدة (دقيقة)</label>
                        <input type="number" x-model.number="form.duration_minutes" min="5" max="480" class="w-full rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-800 px-3 py-2 text-sm">
                    </div>
                    <div x-show="form.schedule_type === 'temporary'">
                        <label class="block text-xs font-bold text-slate-600 mb-1">التاريخ</label>
                        <input type="date" x-model="tempDate" @change="syncTempDate()" class="w-full rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-800 px-3 py-2 text-sm">
                    </div>
                </div>

                <template x-if="form.schedule_type === 'fixed'">
                    <div class="space-y-3 rounded-xl border border-slate-200 dark:border-slate-600 p-3">
                        <div class="grid sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-600 mb-1">الشهر</label>
                                <input type="month" x-model="form.month_key" @change="rebuildMonthDays()" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-600 mb-1">يوم الأسبوع</label>
                                <select x-model.number="form.weekday" @change="rebuildMonthDays()" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                                    @foreach($weekdayLabels as $i => $label)
                                        <option value="{{ $i }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <button type="button" @click="selectAllWeekdaysInMonth()" class="text-xs font-bold px-3 py-1.5 rounded-lg bg-violet-100 text-violet-800">تحديد كل أيام هذا اليوم في الشهر</button>
                            <button type="button" @click="clearSelectedDates()" class="text-xs font-bold px-3 py-1.5 rounded-lg bg-slate-100 text-slate-700">مسح التحديد</button>
                        </div>
                        <div class="grid grid-cols-7 gap-1 text-center text-[10px] font-bold text-slate-400">
                            <template x-for="d in ['أ','إ','ث','ر','خ','ج','س']"><span x-text="d"></span></template>
                        </div>
                        <div class="grid grid-cols-7 gap-1">
                            <template x-for="cell in monthCells" :key="cell.key">
                                <button type="button" x-show="cell.date"
                                        @click="toggleDate(cell.date)"
                                        :class="cell.selected ? 'bg-violet-600 text-white border-violet-600' : (cell.matchesWeekday ? 'bg-violet-50 text-violet-800 border-violet-200' : 'bg-white text-slate-700 border-slate-200')"
                                        class="min-h-[36px] rounded-lg border text-xs font-semibold"
                                        x-text="cell.day"></button>
                                <span x-show="!cell.date" class="min-h-[36px]"></span>
                            </template>
                        </div>
                        <p class="text-[11px] text-slate-500">المحدد: <span x-text="form.selected_dates.length"></span> يوم</p>
                    </div>
                </template>

                <div class="rounded-xl bg-sky-50 dark:bg-sky-900/20 border border-sky-100 dark:border-sky-800 px-3 py-2 text-xs text-sky-900 dark:text-sky-200" x-show="previewText">
                    <i class="fas fa-clock ml-1"></i>
                    <span x-text="previewText"></span>
                </div>

                <div class="grid sm:grid-cols-2 gap-3">
                    <label class="inline-flex items-center gap-2 text-sm">
                        <input type="checkbox" x-model="form.notify_platform" class="rounded text-violet-600">
                        إشعار على المنصة قبل الحصة
                    </label>
                    <label class="inline-flex items-center gap-2 text-sm">
                        <input type="checkbox" x-model="form.notify_email" class="rounded text-violet-600">
                        إرسال تذكير على البريد الإلكتروني
                    </label>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1">التذكير قبل (دقائق)</label>
                    <input type="number" x-model.number="form.reminder_minutes" min="1" max="1440" class="w-32 rounded-xl border border-slate-200 px-3 py-2 text-sm">
                    <p class="text-[10px] text-slate-400 mt-1">من 1 إلى 1440 دقيقة (24 ساعة)</p>
                </div>

                <p x-show="errorText" class="text-sm text-red-600 font-semibold" x-text="errorText"></p>

                <div class="flex flex-wrap gap-2 pt-2">
                    <button type="submit" :disabled="saving" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-violet-600 text-white text-sm font-bold hover:bg-violet-700 disabled:opacity-60">
                        <i class="fas fa-save"></i>
                        <span x-text="saving ? 'جاري الحفظ...' : 'حفظ الموعد'"></span>
                    </button>
                    <button type="button" @click="modalOpen = false" class="px-5 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold">إلغاء</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function teacherPersonalCalendar() {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const weekdayLabels = @json($weekdayLabels);
    const initialTzOptions = @json(collect($timezoneOptions ?? [])->map(fn ($label, $id) => ['id' => $id, 'label' => $label])->values());
    const usStatesMap = @json($usStates ?? []);

    return {
        modalOpen: false,
        detailOpen: false,
        detail: {},
        deleting: false,
        saving: false,
        errorText: '',
        previewText: '',
        tempDate: '',
        monthCells: [],
        familyTzQuery: '',
        teacherTzQuery: '',
        familyTzOptions: initialTzOptions.slice(0, 80),
        teacherTzOptions: initialTzOptions.slice(0, 80),
        usStates: usStatesMap,
        usState: '',
        form: {
            title: '',
            schedule_type: 'temporary',
            family_timezone: 'Europe/Paris',
            teacher_timezone: @json($teacherTimezone),
            family_time: '17:00',
            duration_minutes: 60,
            weekday: 6,
            month_key: new Date().toISOString().slice(0, 7),
            selected_dates: [],
            notify_platform: true,
            notify_email: true,
            reminder_minutes: 5,
        },
        openModal() {
            this.modalOpen = true;
            this.detailOpen = false;
            this.errorText = '';
            this.ensureSelectedInLists();
            this.rebuildMonthDays();
            this.refreshPreview();
        },
        ensureSelectedInLists() {
            const ensure = (list, id, label) => {
                if (!list.some(o => o.id === id)) list.unshift({ id, label: label || id });
            };
            ensure(this.familyTzOptions, this.form.family_timezone, this.form.family_timezone);
            ensure(this.teacherTzOptions, this.form.teacher_timezone, this.form.teacher_timezone);
        },
        async filterFamilyTz() {
            this.familyTzOptions = await this.searchTimezones(this.familyTzQuery);
            this.ensureSelectedInLists();
        },
        async filterTeacherTz() {
            this.teacherTzOptions = await this.searchTimezones(this.teacherTzQuery);
            this.ensureSelectedInLists();
        },
        async searchTimezones(q) {
            try {
                const url = @json(route('calendar.personal.timezones')) + (q ? ('?q=' + encodeURIComponent(q)) : '');
                const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
                const data = await res.json();
                const opts = data.options || {};
                return Object.keys(opts).map(id => ({ id, label: opts[id] }));
            } catch (e) {
                return initialTzOptions.slice(0, 40);
            }
        },
        async applyUsState() {
            if (!this.usState) return;
            try {
                const res = await fetch(@json(route('calendar.personal.us-state')), {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                    body: JSON.stringify({ state: this.usState }),
                });
                const data = await res.json();
                if (!res.ok) throw new Error(data.message || 'تعذر تحديد التوقيت');
                this.form.family_timezone = data.timezone;
                this.familyTzOptions = [{ id: data.timezone, label: data.label }, ...this.familyTzOptions.filter(o => o.id !== data.timezone)];
                this.refreshPreview();
            } catch (e) {
                alert(e.message || 'تعذر تطبيق الولاية');
            }
        },
        openDetail(payload) {
            this.detail = payload || {};
            this.detailOpen = true;
            this.modalOpen = false;
        },
        async deleteDetail() {
            if (!this.detail.appointment_id) return;
            if (!confirm('هل تريد حذف هذا الموعد؟')) return;
            this.deleting = true;
            try {
                const url = '/api/calendar/personal-appointments/' + this.detail.appointment_id;
                const res = await fetch(url, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                });
                const data = await res.json();
                if (!res.ok) throw new Error(data.message || 'تعذر الحذف');
                this.detailOpen = false;
                if (window.studentCalendar) window.studentCalendar.refetchEvents();
                window.location.reload();
            } catch (e) {
                alert(e.message || 'تعذر الحذف');
            } finally {
                this.deleting = false;
            }
        },
        onTypeChange() {
            if (this.form.schedule_type === 'temporary') {
                this.syncTempDate();
            } else {
                this.rebuildMonthDays();
            }
            this.refreshPreview();
        },
        syncTempDate() {
            if (this.tempDate) {
                this.form.selected_dates = [this.tempDate];
            }
            this.refreshPreview();
        },
        rebuildMonthDays() {
            const [y, m] = (this.form.month_key || '').split('-').map(Number);
            if (!y || !m) return;
            const first = new Date(y, m - 1, 1);
            const last = new Date(y, m, 0);
            const startPad = first.getDay();
            const cells = [];
            for (let i = 0; i < startPad; i++) cells.push({ key: 'e'+i, date: null });
            for (let d = 1; d <= last.getDate(); d++) {
                const date = `${y}-${String(m).padStart(2,'0')}-${String(d).padStart(2,'0')}`;
                const dow = new Date(y, m - 1, d).getDay();
                cells.push({
                    key: date,
                    date,
                    day: d,
                    matchesWeekday: dow === Number(this.form.weekday),
                    selected: this.form.selected_dates.includes(date),
                });
            }
            this.monthCells = cells;
        },
        toggleDate(date) {
            const i = this.form.selected_dates.indexOf(date);
            if (i >= 0) this.form.selected_dates.splice(i, 1);
            else this.form.selected_dates.push(date);
            this.form.selected_dates.sort();
            this.rebuildMonthDays();
            this.refreshPreview();
        },
        selectAllWeekdaysInMonth() {
            this.form.selected_dates = this.monthCells.filter(c => c.date && c.matchesWeekday).map(c => c.date);
            this.rebuildMonthDays();
            this.refreshPreview();
        },
        clearSelectedDates() {
            this.form.selected_dates = [];
            this.rebuildMonthDays();
        },
        async refreshPreview() {
            const date = this.form.schedule_type === 'temporary'
                ? (this.form.selected_dates[0] || this.tempDate)
                : (this.form.selected_dates[0] || null);
            if (!date) { this.previewText = ''; return; }
            try {
                const res = await fetch(@json(route('calendar.personal.preview')), {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                    body: JSON.stringify({
                        date,
                        time: this.form.family_time,
                        family_timezone: this.form.family_timezone,
                        teacher_timezone: this.form.teacher_timezone,
                    }),
                });
                const data = await res.json();
                this.previewText = data.preview || '';
            } catch (e) {
                this.previewText = '';
            }
        },
        async submitForm() {
            this.saving = true;
            this.errorText = '';
            try {
                const res = await fetch(@json(route('calendar.personal.store')), {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                    body: JSON.stringify(this.form),
                });
                const data = await res.json();
                if (!res.ok) {
                    const firstErr = data.errors ? Object.values(data.errors).flat()[0] : null;
                    throw new Error(firstErr || data.message || 'تعذر الحفظ');
                }
                this.modalOpen = false;
                if (window.studentCalendar) window.studentCalendar.refetchEvents();
                window.location.reload();
            } catch (e) {
                this.errorText = e.message || 'حدث خطأ';
            } finally {
                this.saving = false;
            }
        },
    };
}
</script>
@endpush
