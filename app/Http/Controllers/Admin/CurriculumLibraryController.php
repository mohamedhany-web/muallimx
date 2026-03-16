<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CurriculumLibraryCategory;
use App\Models\CurriculumLibraryItem;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CurriculumLibraryController extends Controller
{
    public function index(Request $request)
    {
        $query = CurriculumLibraryItem::with('category')->ordered();
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
        $items = $query->paginate(20)->withQueryString();
        $categories = CurriculumLibraryCategory::ordered()->get();

        return view('admin.curriculum-library.index', compact('items', 'categories'));
    }

    public function categories()
    {
        $categories = CurriculumLibraryCategory::withCount('items')->ordered()->get();
        return view('admin.curriculum-library.categories', compact('categories'));
    }

    public function createCategory()
    {
        return view('admin.curriculum-library.categories-form', ['category' => null]);
    }

    public function storeCategory(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:curriculum_library_categories,slug',
            'description' => 'nullable|string',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);
        $validated['slug'] = $validated['slug'] ?? Str::slug($validated['name']);
        $validated['is_active'] = $request->boolean('is_active');
        $validated['order'] = (int) ($validated['order'] ?? 0);
        CurriculumLibraryCategory::create($validated);
        return redirect()->route('admin.curriculum-library.categories')->with('success', 'تم إنشاء التصنيف بنجاح.');
    }

    public function editCategory(CurriculumLibraryCategory $category)
    {
        return view('admin.curriculum-library.categories-form', ['category' => $category]);
    }

    public function updateCategory(Request $request, CurriculumLibraryCategory $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:curriculum_library_categories,slug,' . $category->id,
            'description' => 'nullable|string',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);
        $validated['slug'] = $validated['slug'] ?? Str::slug($validated['name']);
        $validated['is_active'] = $request->boolean('is_active');
        $validated['order'] = (int) ($validated['order'] ?? 0);
        $category->update($validated);
        return redirect()->route('admin.curriculum-library.categories')->with('success', 'تم تحديث التصنيف بنجاح.');
    }

    public function destroyCategory(CurriculumLibraryCategory $category)
    {
        $category->items()->update(['category_id' => null]);
        $category->delete();
        return redirect()->route('admin.curriculum-library.categories')->with('success', 'تم حذف التصنيف.');
    }

    public function createItem()
    {
        $categories = CurriculumLibraryCategory::active()->ordered()->get();
        return view('admin.curriculum-library.items-form', ['item' => null, 'categories' => $categories]);
    }

    public function storeItem(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'nullable|exists:curriculum_library_categories,id',
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:curriculum_library_items,slug',
            'description' => 'nullable|string',
            'content' => 'nullable|string',
            'grade_level' => 'nullable|string|max:50',
            'subject' => 'nullable|string|max:100',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);
        $validated['slug'] = $validated['slug'] ?? Str::slug($validated['title']);
        $validated['is_active'] = $request->boolean('is_active');
        $validated['order'] = (int) ($validated['order'] ?? 0);
        CurriculumLibraryItem::create($validated);
        return redirect()->route('admin.curriculum-library.index')->with('success', 'تم إضافة عنصر المنهج بنجاح.');
    }

    public function editItem(CurriculumLibraryItem $item)
    {
        $categories = CurriculumLibraryCategory::active()->ordered()->get();
        return view('admin.curriculum-library.items-form', ['item' => $item, 'categories' => $categories]);
    }

    public function updateItem(Request $request, CurriculumLibraryItem $item)
    {
        $validated = $request->validate([
            'category_id' => 'nullable|exists:curriculum_library_categories,id',
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:curriculum_library_items,slug,' . $item->id,
            'description' => 'nullable|string',
            'content' => 'nullable|string',
            'grade_level' => 'nullable|string|max:50',
            'subject' => 'nullable|string|max:100',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);
        $validated['slug'] = $validated['slug'] ?? Str::slug($validated['title']);
        $validated['is_active'] = $request->boolean('is_active');
        $validated['order'] = (int) ($validated['order'] ?? 0);
        $item->update($validated);
        return redirect()->route('admin.curriculum-library.index')->with('success', 'تم تحديث عنصر المنهج بنجاح.');
    }

    public function destroyItem(CurriculumLibraryItem $item)
    {
        $item->delete();
        return redirect()->route('admin.curriculum-library.index')->with('success', 'تم حذف عنصر المنهج.');
    }
}
