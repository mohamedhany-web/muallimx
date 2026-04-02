@extends('layouts.admin')

@section('title', 'إصدار شهادة جديدة')
@section('header', 'إصدار شهادة جديدة')

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-200">
        <h1 class="text-2xl font-bold text-gray-900 mb-6">إصدار شهادة جديدة</h1>
        
        <form action="{{ route('admin.certificates.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">الطالب *</label>
                    <select id="certificate-user" name="user_id" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                        <option value="">اختر الطالب</option>
                        @foreach($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }} - {{ $user->phone }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">الكورس *</label>
                    <select id="certificate-course" name="course_id" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                        <option value="">اختر الكورس</option>
                        @foreach($courses as $course)
                        <option value="{{ $course->id }}">{{ $course->title }}</option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-500 mt-2">سيتم عرض كورسات الطالب بعد اختياره.</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">العنوان *</label>
                    <input type="text" name="title" required value="{{ old('title') }}" 
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">تاريخ الإصدار</label>
                    <input type="date" name="issued_at" value="{{ old('issued_at', date('Y-m-d')) }}" 
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">الحالة *</label>
                    <select name="status" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                        <option value="pending" {{ old('status') == 'pending' ? 'selected' : '' }}>معلقة</option>
                        <option value="issued" {{ old('status') == 'issued' ? 'selected' : '' }}>مُصدرة</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">الوصف</label>
                <textarea name="description" rows="4" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sky-500 focus:border-sky-500">{{ old('description') }}</textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">ملف الشهادة (PDF فقط) *</label>
                <input type="file" name="certificate_file" required accept=".pdf,application/pdf"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sky-500 focus:border-sky-500 bg-white">
                @error('certificate_file')
                    <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                @enderror
                <p class="text-xs text-gray-500 mt-2">يُسلَّم للطالب كملف PDF. الحد الأقصى 50 ميجابايت.</p>
            </div>

            <div class="flex gap-4">
                <button type="submit" class="bg-gradient-to-r from-sky-600 to-sky-700 hover:from-sky-700 hover:to-sky-800 text-white px-6 py-3 rounded-lg font-medium transition-colors shadow-lg shadow-sky-500/30">
                    إصدار الشهادة
                </button>
                <a href="{{ route('admin.certificates.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-6 py-3 rounded-lg font-medium transition-colors">
                    إلغاء
                </a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        const userSelect = document.getElementById('certificate-user');
        const courseSelect = document.getElementById('certificate-course');

        if (!userSelect || !courseSelect) return;

        const allCourseOptions = Array.from(courseSelect.querySelectorAll('option')).map(o => ({
            value: o.value,
            text: o.textContent
        }));

        function setCourseOptions(options) {
            courseSelect.innerHTML = '';
            for (const opt of options) {
                const el = document.createElement('option');
                el.value = opt.value;
                el.textContent = opt.text;
                courseSelect.appendChild(el);
            }
        }

        async function loadUserCourses(userId) {
            // reset to placeholder
            setCourseOptions([{ value: '', text: 'اختر الكورس' }]);
            if (!userId) return;

            try {
                const res = await fetch(`{{ url('/admin/certificates/user') }}/${encodeURIComponent(userId)}/courses`, {
                    headers: { 'Accept': 'application/json' }
                });
                if (!res.ok) throw new Error('Failed to load');

                const data = await res.json();
                const courses = Array.isArray(data.courses) ? data.courses : [];

                if (courses.length > 0) {
                    setCourseOptions([{ value: '', text: 'اختر الكورس' }].concat(
                        courses.map(c => ({ value: String(c.id), text: c.title }))
                    ));
                } else {
                    // fallback to all courses if the student has none
                    setCourseOptions(allCourseOptions);
                }
            } catch (e) {
                // fallback to all courses on error
                setCourseOptions(allCourseOptions);
            }
        }

        userSelect.addEventListener('change', () => {
            loadUserCourses(userSelect.value);
        });
    })();
</script>
@endpush

