<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\CurriculumLibraryItem;
use App\Models\CurriculumLibraryItemFile;
use App\Models\CurriculumLibraryPreviewOpen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CurriculumLibraryController extends Controller
{
    /**
     * عرض مكتبة المناهج (مناهج أكس). العميل يشوف القائمة كاملة؛ فتح عنصر واحد فقط مجاناً قبل الاشتراك.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $hasFullAccess = $user && $user->hasSubscriptionFeature('library_access');
        $usedFreePreview = $user ? CurriculumLibraryPreviewOpen::hasUsedFreePreview($user->id) : false;

        $query = CurriculumLibraryItem::active()->with('category')->ordered();
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->filled('language') && in_array($request->language, ['ar', 'en', 'fr'], true)) {
            $query->byLanguage($request->language);
        }
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($qry) use ($q) {
                $qry->where('title', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%")
                    ->orWhere('subject', 'like', "%{$q}%");
            });
        }
        $items = $query->paginate(12)->withQueryString();
        $categories = \App\Models\CurriculumLibraryCategory::active()->ordered()->get();

        return view('student.curriculum-library.index', compact('items', 'categories', 'hasFullAccess', 'usedFreePreview'));
    }

    /**
     * عرض عنصر منهج واحد. قبل الاشتراك: يسمح بفتح ملف واحد فقط (أي عنصر)، ثم يُقفل الباقي.
     */
    public function show(Request $request, CurriculumLibraryItem $item)
    {
        $user = Auth::user();
        $hasFullAccess = $user && $user->hasSubscriptionFeature('library_access');

        if (!$item->is_active) {
            abort(404);
        }

        if (!$hasFullAccess) {
            $usedFreePreview = $user ? CurriculumLibraryPreviewOpen::hasUsedFreePreview($user->id) : false;
            if ($usedFreePreview) {
                return redirect()->route('student.features.show', ['feature' => 'library_access'])
                    ->with('error', 'يمكنك معاينة ملف واحد مجاناً فقط. لفتح باقي مناهج X اشترك في الباقة المناسبة.');
            }
            CurriculumLibraryPreviewOpen::recordFreePreviewUsed($user->id, $item->id);
        }

        $item->load(['category', 'files']);
        return view('student.curriculum-library.show', compact('item', 'hasFullAccess'));
    }

    /**
     * تحميل ملف مرفق بعنصر المنهج (بوربوينت أو وجبة). يسمح فقط لمن له حق فتح هذا العنصر.
     */
    public function download(CurriculumLibraryItem $item, CurriculumLibraryItemFile $file)
    {
        if ($file->curriculum_library_item_id !== $item->id) {
            abort(404);
        }
        if (!$item->is_active) {
            abort(404);
        }

        $user = Auth::user();
        $hasFullAccess = $user && $user->hasSubscriptionFeature('library_access');
        if (!$hasFullAccess) {
            $used = $user ? CurriculumLibraryPreviewOpen::where('user_id', $user->id)->first() : null;
            if (!$used || $used->curriculum_library_item_id != $item->id) {
                return redirect()->route('student.features.show', ['feature' => 'library_access'])
                    ->with('error', 'تحميل الملفات يتطلب اشتراك مناهج X.');
            }
        }

        $path = $file->path;
        if (!$path || !\Illuminate\Support\Facades\Storage::exists($path)) {
            abort(404);
        }

        $filename = $file->label ?: basename($path);
        return \Illuminate\Support\Facades\Storage::download($path, $filename);
    }
}
