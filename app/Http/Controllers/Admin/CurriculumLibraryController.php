<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CurriculumLibraryCategory;
use App\Models\CurriculumLibraryItem;
use App\Models\CurriculumLibraryItemFile;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

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
            'language' => 'nullable|string|in:ar,en,fr',
            'item_type' => 'nullable|string|in:presentation,assignment',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
            'is_free_preview' => 'nullable|boolean',
        ]);
        $validated['slug'] = $validated['slug'] ?? Str::slug($validated['title']);
        $validated['is_active'] = $request->boolean('is_active');
        $validated['is_free_preview'] = $request->boolean('is_free_preview');
        $validated['order'] = (int) ($validated['order'] ?? 0);
        $validated['language'] = $validated['language'] ?? 'ar';
        $validated['item_type'] = $validated['item_type'] ?? 'presentation';
        CurriculumLibraryItem::create($validated);
        return redirect()->route('admin.curriculum-library.index')->with('success', 'تم إضافة عنصر المنهج بنجاح.');
    }

    public function editItem(CurriculumLibraryItem $item)
    {
        $item->load('files');
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
            'language' => 'nullable|string|in:ar,en,fr',
            'item_type' => 'nullable|string|in:presentation,assignment',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
            'is_free_preview' => 'nullable|boolean',
        ]);
        $validated['slug'] = $validated['slug'] ?? Str::slug($validated['title']);
        $validated['is_active'] = $request->boolean('is_active');
        $validated['is_free_preview'] = $request->boolean('is_free_preview');
        $validated['order'] = (int) ($validated['order'] ?? 0);
        $validated['language'] = $validated['language'] ?? 'ar';
        $validated['item_type'] = $validated['item_type'] ?? 'presentation';
        $item->update($validated);

        $this->storeItemFiles($request, $item);

        return redirect()->route('admin.curriculum-library.index')->with('success', 'تم تحديث عنصر المنهج بنجاح.');
    }

    public function destroyItem(CurriculumLibraryItem $item)
    {
        foreach ($item->files as $file) {
            if ($file->path && Storage::exists($file->path)) {
                Storage::delete($file->path);
            }
        }
        $item->delete();
        return redirect()->route('admin.curriculum-library.index')->with('success', 'تم حذف عنصر المنهج.');
    }

    /**
     * رفع ملفات جديدة لعنصر المنهج (بوربوينت / وجبات).
     */
    protected function storeItemFiles(Request $request, CurriculumLibraryItem $item): void
    {
        $newFiles = $request->file('new_files');
        $types = $request->input('new_files_type', []);
        $labels = $request->input('new_files_label', []);
        if (!is_array($newFiles)) {
            return;
        }
        $order = ($item->files()->max('order') ?? 0) + 1;
        foreach ($newFiles as $i => $file) {
            if (!$file || !$file->isValid()) {
                continue;
            }
            $path = $file->store('curriculum-library/' . $item->id, 'public');
            $type = $types[$i] ?? 'presentation';
            if (!in_array($type, ['presentation', 'assignment'], true)) {
                $type = 'presentation';
            }
            $label = $labels[$i] ?? null;
            CurriculumLibraryItemFile::create([
                'curriculum_library_item_id' => $item->id,
                'path' => $path,
                'label' => $label ?: null,
                'file_type' => $type,
                'order' => $order++,
            ]);
        }
    }

    public function destroyFile(CurriculumLibraryItem $item, CurriculumLibraryItemFile $file)
    {
        if ($file->curriculum_library_item_id !== $item->id) {
            abort(404);
        }
        if ($file->path && Storage::exists($file->path)) {
            Storage::delete($file->path);
        }
        $file->delete();
        return redirect()->route('admin.curriculum-library.items.edit', $item)->with('success', 'تم حذف الملف.');
    }
}
