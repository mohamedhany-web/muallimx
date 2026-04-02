<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\AdvancedCourse;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\StudentCourseEnrollment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class CertificateController extends Controller
{
    private function filterCertificateTableColumns(array $data): array
    {
        try {
            $columns = Schema::getColumnListing('certificates');
            $allowed = array_fill_keys($columns, true);
            return array_intersect_key($data, $allowed);
        } catch (\Throwable $e) {
            // If the DB connection/schema introspection fails, fallback to original data.
            return $data;
        }
    }

    private function getCertificatesCourseReferenceTable(): ?string
    {
        try {
            $dbName = DB::getDatabaseName();
            $row = DB::selectOne(
                "SELECT REFERENCED_TABLE_NAME AS ref_table
                 FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
                 WHERE TABLE_SCHEMA = ?
                   AND TABLE_NAME = 'certificates'
                   AND COLUMN_NAME = 'course_id'
                   AND REFERENCED_TABLE_NAME IS NOT NULL
                 LIMIT 1",
                [$dbName]
            );
            $ref = $row?->ref_table ?? null;
            return is_string($ref) && $ref !== '' ? $ref : null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function resolveCourseName(?int $courseId, ?string $refTable): string
    {
        if (!$courseId) {
            return '';
        }

        if ($refTable === 'courses') {
            return (string) (Course::find($courseId)->title ?? '');
        }

        // default to advanced_courses
        return (string) (AdvancedCourse::find($courseId)->title ?? '');
    }

    public function userCourses(User $user)
    {
        // Basic authorization: admin middleware already applied at route group level
        $refTable = $this->getCertificatesCourseReferenceTable();

        if ($refTable === 'courses') {
            $courseIds = CourseEnrollment::where('student_id', $user->id)
                ->whereNotNull('course_id')
                ->pluck('course_id')
                ->unique()
                ->values()
                ->all();

            $courses = Course::whereIn('id', $courseIds)
                ->orderBy('title')
                ->get(['id', 'title']);
        } else {
            // default to advanced_courses
            $courseIdsFromStudentEnrollments = StudentCourseEnrollment::where('user_id', $user->id)
                ->whereIn('status', ['active', 'completed'])
                ->pluck('advanced_course_id')
                ->unique()
                ->values()
                ->all();

            // Some installs also store enrollments in course_enrollments. Merge defensively.
            $courseIdsFromCourseEnrollments = CourseEnrollment::where('student_id', $user->id)
                ->whereNotNull('advanced_course_id')
                ->where('is_active', true)
                ->pluck('advanced_course_id')
                ->unique()
                ->values()
                ->all();

            $courseIds = array_values(array_unique(array_merge($courseIdsFromStudentEnrollments, $courseIdsFromCourseEnrollments)));

            $courses = AdvancedCourse::whereIn('id', $courseIds)
                ->orderBy('title')
                ->get(['id', 'title']);
        }

        return response()->json([
            'refTable' => $refTable ?? 'advanced_courses',
            'courses' => $courses,
        ]);
    }

    public function index(Request $request)
    {
        $query = Certificate::with(['user', 'course'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            if ($request->status == 'issued') {
                $query->where(function($q) {
                    $q->where('status', 'issued')->orWhere('is_verified', true);
                });
            } elseif ($request->status == 'pending') {
                $query->where(function($q) {
                    $q->where('status', 'pending')->orWhere('is_verified', false);
                });
            } else {
                $query->where('status', $request->status);
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('certificate_number', 'like', "%{$search}%")
                  ->orWhereHas('user', function($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $certificates = $query->paginate(20);

        $stats = [
            'total' => Certificate::count(),
            'issued' => Certificate::where(function($q) {
                $q->where('status', 'issued')->orWhere('is_verified', true);
            })->count(),
            'pending' => Certificate::where(function($q) {
                $q->where('status', 'pending')->orWhere('is_verified', false);
            })->count(),
        ];

        return view('admin.certificates.index', compact('certificates', 'stats'));
    }

    public function create()
    {
        $users = User::where('role', 'student')->where('is_active', true)->get();
        $refTable = $this->getCertificatesCourseReferenceTable();

        if ($refTable === 'courses') {
            $courses = Course::orderBy('id', 'desc')->get();
        } else {
            $courses = AdvancedCourse::where('is_active', true)->get();
        }

        return view('admin.certificates.create', compact('users', 'courses', 'refTable'));
    }

    public function store(Request $request)
    {
        $refTable = $this->getCertificatesCourseReferenceTable();

        $courseExistsRule = ($refTable === 'courses')
            ? Rule::exists('courses', 'id')
            : Rule::exists('advanced_courses', 'id');

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'course_id' => ['required', 'integer', $courseExistsRule],
            'title' => 'required|string',
            'description' => 'nullable|string',
            'issued_at' => 'nullable|date',
            'status' => 'required|in:pending,issued,revoked',
            'certificate_file' => 'required|file|mimes:pdf|max:51200',
        ]);

        $courseId = (int) $validated['course_id'];

        $storedPath = null;
        if ($request->hasFile('certificate_file')) {
            $file = $request->file('certificate_file');
            $fileName = 'cert-' . Str::uuid()->toString() . '.pdf';
            $storedPath = $file->storeAs('certificates/' . (int) $validated['user_id'], $fileName, 'public');
        }

        $createData = [
            'certificate_number' => 'CERT-' . str_pad(Certificate::count() + 1, 8, '0', STR_PAD_LEFT),
            'user_id' => $validated['user_id'],
            'course_id' => $courseId,
            'course_name' => $this->resolveCourseName($courseId, $refTable),
            'certificate_type' => 'completion',
            'title' => $validated['title'] ?? '',
            'description' => $validated['description'] ?? null,
            'issue_date' => $validated['issued_at'] ?? now(),
            'issued_at' => $validated['issued_at'] ?? now(),
            'verification_code' => strtoupper(uniqid('CERT')),
            'status' => $validated['status'] ?? 'pending',
            'is_verified' => $validated['status'] === 'issued',
            'instructor_id' => $validated['instructor_id'] ?? null,
            'academy_signature_name' => $validated['academy_signature_name'] ?? 'المدير العام',
            'academy_signature_title' => $validated['academy_signature_title'] ?? 'MuallimX',
            'instructor_signature_name' => $validated['instructor_signature_name'] ?? null,
            'instructor_signature_title' => $validated['instructor_signature_title'] ?? 'المدرب المعتمد',
            'pdf_path' => $storedPath,
        ];

        // If some extended columns don't exist in the DB, preserve them inside metadata
        $metadata = [];
        if (!Schema::hasColumn('certificates', 'title')) {
            $metadata['title'] = $createData['title'] ?? null;
            unset($createData['title']);
        }
        if (!Schema::hasColumn('certificates', 'description')) {
            $metadata['description'] = $createData['description'] ?? null;
            unset($createData['description']);
        }
        if (!Schema::hasColumn('certificates', 'status')) {
            $metadata['status'] = $createData['status'] ?? null;
            unset($createData['status']);
        }
        if (!Schema::hasColumn('certificates', 'issued_at')) {
            $metadata['issued_at'] = $createData['issued_at'] ?? null;
            unset($createData['issued_at']);
        }
        if (!Schema::hasColumn('certificates', 'instructor_id')) {
            $metadata['instructor_id'] = $createData['instructor_id'] ?? null;
            unset($createData['instructor_id']);
        }
        if (!Schema::hasColumn('certificates', 'academy_signature_name')) {
            $metadata['academy_signature_name'] = $createData['academy_signature_name'] ?? null;
            unset($createData['academy_signature_name']);
        }
        if (!Schema::hasColumn('certificates', 'academy_signature_title')) {
            $metadata['academy_signature_title'] = $createData['academy_signature_title'] ?? null;
            unset($createData['academy_signature_title']);
        }
        if (!Schema::hasColumn('certificates', 'instructor_signature_name')) {
            $metadata['instructor_signature_name'] = $createData['instructor_signature_name'] ?? null;
            unset($createData['instructor_signature_name']);
        }
        if (!Schema::hasColumn('certificates', 'instructor_signature_title')) {
            $metadata['instructor_signature_title'] = $createData['instructor_signature_title'] ?? null;
            unset($createData['instructor_signature_title']);
        }

        // Some DBs may not have the serial_number column (migration not applied).
        if (Schema::hasColumn('certificates', 'serial_number')) {
            $createData['serial_number'] = Certificate::generateSerialNumber();
        } else {
            $metadata['serial_number'] = Certificate::generateSerialNumber();
        }

        if (!empty($metadata) && Schema::hasColumn('certificates', 'metadata')) {
            $createData['metadata'] = $metadata;
        }

        $certificate = Certificate::create($this->filterCertificateTableColumns($createData));

        // Generate certificate hash and verification URL only if columns exist
        if ($validated['status'] === 'issued') {
            $needsSave = false;
            if (Schema::hasColumn('certificates', 'certificate_hash')) {
                $certificate->certificate_hash = $certificate->generateHash();
                $needsSave = true;
            }
            if (Schema::hasColumn('certificates', 'verification_url')) {
                $certificate->verification_url = $certificate->verification_url;
                $needsSave = true;
            }
            if (Schema::hasColumn('certificates', 'certified_at')) {
                $certificate->certified_at = now();
                $needsSave = true;
            }
            if ($needsSave) {
                $certificate->save();
            }
        }

        return redirect()->route('admin.certificates.index')
            ->with('success', 'تم إنشاء الشهادة بنجاح');
    }

    public function show(Certificate $certificate)
    {
        $certificate->load(['user', 'course']);

        return view('admin.certificates.show', compact('certificate'));
    }

    public function file(Certificate $certificate)
    {
        return $this->serveCertificateFile($certificate, false);
    }

    public function download(Certificate $certificate)
    {
        return $this->serveCertificateFile($certificate, true);
    }

    private function serveCertificateFile(Certificate $certificate, bool $asAttachment)
    {
        if (empty($certificate->pdf_path)) {
            abort(404, 'لا يوجد ملف مرفوع لهذه الشهادة.');
        }

        $disk = Storage::disk('public');
        if (! $disk->exists($certificate->pdf_path)) {
            abort(404, 'ملف الشهادة غير موجود على الخادم.');
        }

        $ext = strtolower(pathinfo($certificate->pdf_path, PATHINFO_EXTENSION) ?: 'pdf');
        $base = 'MuallimX-certificate-' . preg_replace('/[^A-Za-z0-9._-]+/', '_', (string) ($certificate->certificate_number ?? $certificate->id));
        $downloadName = $base . '.' . ($ext ?: 'pdf');

        if ($asAttachment) {
            return $disk->download($certificate->pdf_path, $downloadName);
        }

        return $disk->response($certificate->pdf_path, $downloadName, [
            'Content-Disposition' => 'inline; filename="' . $downloadName . '"',
        ]);
    }

    public function edit(Certificate $certificate)
    {
        $users = User::where('role', 'student')->where('is_active', true)->get();
        $refTable = $this->getCertificatesCourseReferenceTable();

        if ($refTable === 'courses') {
            $courses = Course::orderBy('id', 'desc')->get();
        } else {
            $courses = AdvancedCourse::where('is_active', true)->get();
        }

        return view('admin.certificates.edit', compact('certificate', 'users', 'courses', 'refTable'));
    }

    public function update(Request $request, Certificate $certificate)
    {
        $refTable = $this->getCertificatesCourseReferenceTable();

        $courseExistsRule = ($refTable === 'courses')
            ? Rule::exists('courses', 'id')
            : Rule::exists('advanced_courses', 'id');

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'course_id' => ['required', 'integer', $courseExistsRule],
            'title' => 'required|string',
            'description' => 'nullable|string',
            'issued_at' => 'nullable|date',
            'status' => 'required|in:pending,issued,revoked',
            'certificate_file' => 'nullable|file|mimes:pdf|max:51200',
        ]);

        $updateData = $validated;
        if (isset($validated['issued_at'])) {
            $updateData['issue_date'] = $validated['issued_at'];
        }

        $courseId = (int) $validated['course_id'];
        $updateData['course_id'] = $courseId;
        $updateData['course_name'] = $this->resolveCourseName($courseId, $refTable);

        if (isset($validated['status'])) {
            $updateData['is_verified'] = $validated['status'] === 'issued';
        }

        if ($request->hasFile('certificate_file')) {
            $file = $request->file('certificate_file');
            $fileName = 'cert-' . Str::uuid()->toString() . '.pdf';
            $newPath = $file->storeAs('certificates/' . (int) $validated['user_id'], $fileName, 'public');

            if ($certificate->pdf_path) {
                try {
                    Storage::disk('public')->delete($certificate->pdf_path);
                } catch (\Throwable $e) {
                    // ignore delete failures
                }
            }
            $updateData['pdf_path'] = $newPath;
        }

        // Preserve extended fields in metadata if columns do not exist
        $meta = [];
        if (Schema::hasColumn('certificates', 'metadata')) {
            $meta = is_array($certificate->metadata ?? null) ? ($certificate->metadata ?? []) : [];
        }

        foreach (['title', 'description', 'status', 'issued_at'] as $field) {
            if (array_key_exists($field, $updateData) && !Schema::hasColumn('certificates', $field)) {
                $meta[$field] = $updateData[$field];
                unset($updateData[$field]);
            }
        }

        if (!empty($meta) && Schema::hasColumn('certificates', 'metadata')) {
            $updateData['metadata'] = $meta;
        }

        $certificate->update($this->filterCertificateTableColumns($updateData));

        if (($validated['status'] ?? '') === 'issued') {
            $needsSave = false;
            if (Schema::hasColumn('certificates', 'certificate_hash')) {
                $certificate->certificate_hash = $certificate->generateHash();
                $needsSave = true;
            }
            if (Schema::hasColumn('certificates', 'certified_at') && ! $certificate->certified_at) {
                $certificate->certified_at = now();
                $needsSave = true;
            }
            if ($needsSave) {
                $certificate->save();
            }
        }

        return redirect()->route('admin.certificates.index')
            ->with('success', 'تم تحديث الشهادة بنجاح');
    }

    public function destroy(Certificate $certificate)
    {
        if ($certificate->pdf_path) {
            try {
                Storage::disk('public')->delete($certificate->pdf_path);
            } catch (\Throwable $e) {
            }
        }
        $certificate->delete();

        return redirect()->route('admin.certificates.index')
            ->with('success', 'تم حذف الشهادة والملف المرفق بنجاح');
    }
}
