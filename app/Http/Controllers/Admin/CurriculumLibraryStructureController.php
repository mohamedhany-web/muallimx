<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CurriculumLibraryItem;
use App\Models\CurriculumLibraryMaterial;
use App\Models\CurriculumLibrarySection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CurriculumLibraryStructureController extends Controller
{
    public function show(CurriculumLibraryItem $item)
    {
        $tree = CurriculumLibrarySection::treeForItem($item, false);
        $flatSections = $item->sections()->withCount('materials')->orderBy('order')->orderBy('id')->get();

        return view('admin.curriculum-library.structure', compact('item', 'tree', 'flatSections'));
    }

    public function storeSection(Request $request, CurriculumLibraryItem $item)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:curriculum_library_sections,id',
            'order' => 'nullable|integer|min:0',
        ]);

        if (!empty($validated['parent_id'])) {
            CurriculumLibrarySection::where('id', $validated['parent_id'])
                ->where('curriculum_library_item_id', $item->id)
                ->firstOrFail();
        }

        CurriculumLibrarySection::create([
            'curriculum_library_item_id' => $item->id,
            'parent_id' => $validated['parent_id'] ?? null,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'order' => (int) ($validated['order'] ?? 0),
            'is_active' => true,
        ]);

        return redirect()->route('admin.curriculum-library.items.structure', $item)
            ->with('success', 'تم إنشاء القسم.');
    }

    public function updateSection(Request $request, CurriculumLibraryItem $item, CurriculumLibrarySection $section)
    {
        $this->assertSectionBelongs($item, $section);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:curriculum_library_sections,id',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $newParentId = $validated['parent_id'] ?? null;
        if ($newParentId !== null && (int) $newParentId === (int) $section->id) {
            return back()->with('error', 'لا يمكن جعل القسم أباً لنفسه.');
        }

        if ($newParentId !== null) {
            CurriculumLibrarySection::where('id', $newParentId)
                ->where('curriculum_library_item_id', $item->id)
                ->firstOrFail();

            if ($this->wouldCreateCycle($section, (int) $newParentId)) {
                return back()->with('error', 'لا يمكن نقل القسم تحت أحد أبنائه.');
            }
        }

        $section->update([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'parent_id' => $newParentId,
            'order' => (int) ($validated['order'] ?? 0),
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.curriculum-library.items.structure', $item)
            ->with('success', 'تم تحديث القسم.');
    }

    public function destroySection(CurriculumLibraryItem $item, CurriculumLibrarySection $section)
    {
        $this->assertSectionBelongs($item, $section);
        $section->deleteWithStorage();

        return redirect()->route('admin.curriculum-library.items.structure', $item)
            ->with('success', 'تم حذف القسم وما بداخله.');
    }

    public function storeMaterial(Request $request, CurriculumLibraryItem $item, CurriculumLibrarySection $section)
    {
        $this->assertSectionBelongs($item, $section);

        $request->validate([
            'file' => 'required|file|max:51200',
            'title' => 'nullable|string|max:255',
            'view_in_platform' => 'nullable|boolean',
            'allow_download' => 'nullable|boolean',
        ]);

        try {
            $upload = $request->file('file');
            $ext = strtolower((string) $upload->getClientOriginalExtension());
            $fileKind = CurriculumLibraryMaterial::fileKindFromExtension($ext);

            $viewIn = $request->boolean('view_in_platform');
            $allowDl = $request->boolean('allow_download');

            if ($fileKind === 'html') {
                $viewIn = true;
                $allowDl = false;
            }
            if ($fileKind === 'pptx') {
                $allowDl = false;
            }
            if ($fileKind === 'other') {
                $viewIn = false;
            }

            $path = $upload->store('curriculum-library/materials/'.$section->id, 'r2');
            $order = ((int) ($section->materials()->max('order') ?? 0)) + 1;

            CurriculumLibraryMaterial::create([
                'curriculum_library_section_id' => $section->id,
                'title' => $request->input('title') ?: null,
                'path' => $path,
                'storage_disk' => 'r2',
                'original_name' => $upload->getClientOriginalName(),
                'file_kind' => $fileKind,
                'view_in_platform' => $viewIn,
                'allow_download' => $allowDl,
                'order' => $order,
                'is_active' => true,
            ]);
        } catch (\Throwable $e) {
            Log::error('Curriculum material upload failed', [
                'item_id' => $item->id,
                'section_id' => $section->id,
                'user_id' => auth()->id(),
                'message' => $e->getMessage(),
            ]);

            return redirect()->route('admin.curriculum-library.items.structure', $item)
                ->with('error', 'فشل رفع الملف. تحقق من إعدادات Cloudflare R2 أو نوع الملف ثم أعد المحاولة.');
        }

        return redirect()->route('admin.curriculum-library.items.structure', $item)
            ->with('success', 'تم رفع المادة إلى Cloudflare R2.');
    }

    public function updateMaterial(Request $request, CurriculumLibraryItem $item, CurriculumLibraryMaterial $material)
    {
        $this->assertMaterialBelongs($item, $material);

        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'view_in_platform' => 'nullable|boolean',
            'allow_download' => 'nullable|boolean',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $fileKind = $material->file_kind;

        $viewIn = $request->boolean('view_in_platform');
        $allowDl = $request->boolean('allow_download');

        if ($fileKind === 'html') {
            $viewIn = true;
            $allowDl = false;
        }
        if ($fileKind === 'pptx') {
            $allowDl = false;
        }
        if ($fileKind === 'other') {
            $viewIn = false;
        }

        $material->update([
            'title' => $validated['title'] ?? $material->title,
            'view_in_platform' => $viewIn,
            'allow_download' => $allowDl,
            'order' => (int) ($validated['order'] ?? $material->order),
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.curriculum-library.items.structure', $item)
            ->with('success', 'تم تحديث المادة.');
    }

    public function destroyMaterial(CurriculumLibraryItem $item, CurriculumLibraryMaterial $material)
    {
        $this->assertMaterialBelongs($item, $material);

        $disk = $material->storage_disk ?: 'r2';
        if ($material->path && Storage::disk($disk)->exists($material->path)) {
            Storage::disk($disk)->delete($material->path);
        }
        $material->delete();

        return redirect()->route('admin.curriculum-library.items.structure', $item)
            ->with('success', 'تم حذف المادة.');
    }

    protected function assertSectionBelongs(CurriculumLibraryItem $item, CurriculumLibrarySection $section): void
    {
        if ((int) $section->curriculum_library_item_id !== (int) $item->id) {
            abort(404);
        }
    }

    protected function assertMaterialBelongs(CurriculumLibraryItem $item, CurriculumLibraryMaterial $material): void
    {
        $material->loadMissing('section');
        if (!$material->section || (int) $material->section->curriculum_library_item_id !== (int) $item->id) {
            abort(404);
        }
    }

    protected function wouldCreateCycle(CurriculumLibrarySection $section, int $newParentId): bool
    {
        $walk = $newParentId;
        $guard = 0;
        while ($walk && $guard++ < 200) {
            if ((int) $walk === (int) $section->id) {
                return true;
            }
            $walk = (int) (CurriculumLibrarySection::where('id', $walk)->value('parent_id') ?? 0);
        }

        return false;
    }
}
