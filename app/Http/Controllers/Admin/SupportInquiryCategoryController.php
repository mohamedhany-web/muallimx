<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportInquiryCategory;
use Illuminate\Http\Request;

class SupportInquiryCategoryController extends Controller
{
    public function index()
    {
        $categories = SupportInquiryCategory::query()->orderBy('sort_order')->orderBy('name')->get();

        return view('admin.support-inquiry-categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        SupportInquiryCategory::create([
            'name' => $data['name'],
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.support-inquiry-categories.index')
            ->with('success', 'تم إضافة التصنيف.');
    }

    public function update(Request $request, SupportInquiryCategory $support_inquiry_category)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $support_inquiry_category->update([
            'name' => $data['name'],
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.support-inquiry-categories.index')
            ->with('success', 'تم تحديث التصنيف.');
    }

    public function destroy(SupportInquiryCategory $support_inquiry_category)
    {
        $support_inquiry_category->delete();

        return redirect()->route('admin.support-inquiry-categories.index')
            ->with('success', 'تم حذف التصنيف. التذاكر المرتبطة أصبحت دون تصنيف.');
    }
}
