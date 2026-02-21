<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CommunityCompetition;
use App\Models\CommunityDataset;
use App\Models\User;
use App\Services\Community\DatasetFileReaderService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * إدارة مجتمع البيانات والذكاء الاصطناعي — للإدارة العليا فقط.
 * (لوحة المجتمع، مسابقات، مجموعات بيانات، تقديمات، مساهمون، مناقشات، إعدادات)
 */
class CommunityController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:super_admin');
    }

    public function dashboard(): View
    {
        $stats = [
            'competitions_count' => CommunityCompetition::count(),
            'competitions_active' => CommunityCompetition::active()->count(),
            'datasets_count' => CommunityDataset::count(),
            'datasets_active' => CommunityDataset::active()->count(),
            'pending_submissions' => CommunityDataset::pending()->count(),
        ];
        $recentCompetitions = CommunityCompetition::ordered()->take(4)->get();
        $recentDatasets = CommunityDataset::approved()->ordered()->take(4)->get();

        return view('admin.community.dashboard', [
            'stats' => $stats,
            'recentCompetitions' => $recentCompetitions,
            'recentDatasets' => $recentDatasets,
        ]);
    }

    public function competitions(): View
    {
        return view('admin.community.coming-soon', ['section' => 'competitions']);
    }

    public function datasets(): View
    {
        return view('admin.community.coming-soon', ['section' => 'datasets']);
    }

    public function submissions(): View
    {
        $pendingDatasets = CommunityDataset::pending()->with('creator')->ordered()->get();
        return view('admin.community.submissions', ['pendingDatasets' => $pendingDatasets]);
    }

    /**
     * عرض تقديم مجموعة بيانات: الوصف، معاينة الملف، روابط التحميل.
     */
    public function showSubmission(DatasetFileReaderService $reader, CommunityDataset $dataset): View
    {
        $dataset->load('creator');
        $disk = community_disk();
        $preview = ['headers' => [], 'rows' => []];
        if ($dataset->file_path) {
            $preview = $reader->readPreviewFromStorage($disk, $dataset->file_path);
        }
        return view('admin.community.submissions-show', [
            'dataset' => $dataset,
            'previewHeaders' => $preview['headers'],
            'previewRows' => $preview['rows'],
        ]);
    }

    /**
     * تحميل ملف مجموعة البيانات (للمراجعة).
     */
    public function downloadSubmission(CommunityDataset $dataset): StreamedResponse
    {
        if (!$dataset->file_path) {
            abort(404);
        }
        $disk = community_disk();
        if (!Storage::disk($disk)->exists($dataset->file_path)) {
            abort(404);
        }
        $name = basename($dataset->file_path);
        return Storage::disk($disk)->download($dataset->file_path, $name);
    }

    public function approveDataset(Request $request, CommunityDataset $dataset): RedirectResponse
    {
        if ($dataset->status !== CommunityDataset::STATUS_PENDING) {
            return back()->with('error', 'هذا العنصر تمت مراجعته مسبقاً.');
        }
        $dataset->update(['status' => CommunityDataset::STATUS_APPROVED, 'is_active' => true]);
        return redirect()->route('admin.community.submissions.index')->with('success', 'تمت الموافقة على مجموعة البيانات ونشرها.');
    }

    public function rejectDataset(Request $request, CommunityDataset $dataset): RedirectResponse
    {
        if ($dataset->status !== CommunityDataset::STATUS_PENDING) {
            return back()->with('error', 'هذا العنصر تمت مراجعته مسبقاً.');
        }
        $dataset->update(['status' => CommunityDataset::STATUS_REJECTED]);
        return redirect()->route('admin.community.submissions.index')->with('success', 'تم رفض مجموعة البيانات.');
    }

    public function contributors(): View
    {
        $contributors = User::where('is_community_contributor', true)->orderBy('name')->get();
        return view('admin.community.contributors', ['contributors' => $contributors]);
    }

    public function addContributor(Request $request): RedirectResponse
    {
        $email = $request->input('email');
        $userId = $request->input('user_id');

        if (!filled($email) && !filled($userId)) {
            return back()->withErrors(['email' => 'يجب إدخال البريد الإلكتروني للمستخدم.'])->withInput();
        }

        if (filled($userId)) {
            $request->validate(['user_id' => 'exists:users,id'], ['user_id.exists' => 'المستخدم غير موجود.']);
            $user = User::findOrFail($userId);
        } else {
            $request->validate([
                'email' => 'required|email|exists:users,email',
            ], [
                'email.required' => 'يجب إدخال البريد الإلكتروني.',
                'email.email' => 'صيغة البريد غير صحيحة.',
                'email.exists' => 'لا يوجد حساب بهذا البريد.',
            ]);
            $user = User::where('email', $request->email)->firstOrFail();
        }

        $user->update(['is_community_contributor' => true]);
        return redirect()->route('admin.community.contributors.index')->with('success', 'تمت إضافة المساهم: ' . $user->name);
    }

    public function removeContributor(User $user): RedirectResponse
    {
        $user->update(['is_community_contributor' => false]);
        return redirect()->route('admin.community.contributors.index')->with('success', 'تمت إزالة صلاحية المساهم.');
    }

    /**
     * إنشاء حساب مساهم جديد (مستخدم جديد بصلاحية مساهم فقط).
     */
    public function storeNewContributor(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:50',
        ], [
            'name.required' => 'الاسم مطلوب.',
            'email.required' => 'البريد الإلكتروني مطلوب.',
            'email.unique' => 'هذا البريد مسجل مسبقاً.',
            'password.required' => 'كلمة المرور مطلوبة.',
            'password.min' => 'كلمة المرور 8 أحرف على الأقل.',
            'password.confirmed' => 'تأكيد كلمة المرور غير مطابق.',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => Hash::make($validated['password']),
            'role' => 'student',
            'is_community_contributor' => true,
            'is_active' => true,
        ]);

        return redirect()->route('admin.community.contributors.index')
            ->with('success', 'تم إنشاء حساب المساهم: ' . $user->name . ' — يمكنه تسجيل الدخول من صفحة المجتمع.');
    }

    public function discussions(): View
    {
        return view('admin.community.coming-soon', ['section' => 'discussions']);
    }

    public function settings(): View
    {
        return view('admin.community.coming-soon', ['section' => 'settings']);
    }
}
