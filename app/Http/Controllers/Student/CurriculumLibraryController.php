<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\CurriculumLibraryItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CurriculumLibraryController extends Controller
{
    /**
     * عرض مكتبة المناهج (للمستخدمين الذين لديهم ميزة library_access).
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        if (!$user->hasSubscriptionFeature('library_access')) {
            return redirect()->route('student.features.show', ['feature' => 'library_access'])
                ->with('error', 'الوصول إلى مكتبة المناهج التفاعلية يتطلب اشتراكاً في إحدى باقات المعلمين.');
        }

        $query = CurriculumLibraryItem::active()->with('category')->ordered();
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
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

        return view('student.curriculum-library.index', compact('items', 'categories'));
    }

    /**
     * عرض عنصر منهج واحد.
     */
    public function show(Request $request, CurriculumLibraryItem $item)
    {
        $user = Auth::user();
        if (!$user->hasSubscriptionFeature('library_access')) {
            return redirect()->route('student.features.show', ['feature' => 'library_access'])
                ->with('error', 'الوصول إلى مكتبة المناهج التفاعلية يتطلب اشتراكاً في إحدى باقات المعلمين.');
        }

        if (!$item->is_active) {
            abort(404);
        }

        $item->load('category');
        return view('student.curriculum-library.show', compact('item'));
    }
}
