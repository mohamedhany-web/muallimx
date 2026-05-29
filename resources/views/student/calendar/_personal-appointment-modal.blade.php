@php
    $weekdayLabels = ['الأحد', 'الإثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة', 'السبت'];
@endphp
<div x-cloak>
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
                        <select x-model="form.family_timezone" @change="refreshPreview()" class="w-full rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-800 px-3 py-2 text-sm">
                            @foreach($timezoneOptions as $tz => $label)
                                <option value="{{ $tz }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1">توقيتك (العرض)</label>
                        <select x-model="form.teacher_timezone" @change="refreshPreview()" class="w-full rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-800 px-3 py-2 text-sm">
                            @foreach($timezoneOptions as $tz => $label)
                                <option value="{{ $tz }}" @selected($tz === $teacherTimezone)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
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
                        إرسال تذكير على Gmail
                    </label>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1">التذكير قبل (دقائق)</label>
                    <input type="number" x-model.number="form.reminder_minutes" min="1" max="120" class="w-32 rounded-xl border border-slate-200 px-3 py-2 text-sm">
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

    return {
        modalOpen: false,
        saving: false,
        errorText: '',
        previewText: '',
        tempDate: '',
        monthCells: [],
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
            this.errorText = '';
            this.rebuildMonthDays();
            this.refreshPreview();
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
                if (!res.ok) throw new Error(data.message || 'تعذر الحفظ');
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
