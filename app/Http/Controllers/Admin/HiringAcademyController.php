<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HiringAcademy;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class HiringAcademyController extends Controller
{
    public function index(Request $request)
    {
        $q = HiringAcademy::query()->withCount('opportunities');

        if ($request->filled('search')) {
            $s = $request->search;
            $q->where(function ($qq) use ($s) {
                $qq->where('name', 'like', "%{$s}%")
                    ->orWhere('legal_name', 'like', "%{$s}%")
                    ->orWhere('contact_email', 'like', "%{$s}%")
                    ->orWhere('contact_phone', 'like', "%{$s}%")
                    ->orWhere('city', 'like', "%{$s}%");
            });
        }

        if ($request->filled('status') && in_array($request->status, array_keys(HiringAcademy::statusLabels()), true)) {
            $q->where('status', $request->status);
        }

        $academies = $q->latest()->paginate(18)->withQueryString();

        $stats = [
            'total' => HiringAcademy::count(),
            'active' => HiringAcademy::where('status', HiringAcademy::STATUS_ACTIVE)->count(),
            'lead' => HiringAcademy::where('status', HiringAcademy::STATUS_LEAD)->count(),
            'opportunities' => \App\Models\AcademyOpportunity::count(),
        ];

        return view('admin.hiring-academies.index', compact('academies', 'stats'));
    }

    public function create()
    {
        return view('admin.hiring-academies.create');
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['slug'] = HiringAcademy::generateUniqueSlug($data['name']);
        $data['created_by'] = auth()->id();
        HiringAcademy::create($data);

        return redirect()->route('admin.hiring-academies.index')->with('success', __('admin.hiring_academy_created'));
    }

    public function show(HiringAcademy $hiring_academy)
    {
        $hiring_academy->loadCount('opportunities');
        $hiring_academy->load(['opportunities' => fn ($q) => $q->withCount('applications', 'teacherPresentations')->latest()->limit(50)]);

        return view('admin.hiring-academies.show', ['academy' => $hiring_academy]);
    }

    public function edit(HiringAcademy $hiring_academy)
    {
        return view('admin.hiring-academies.edit', ['academy' => $hiring_academy]);
    }

    public function update(Request $request, HiringAcademy $hiring_academy)
    {
        $data = $this->validated($request);
        if ($data['name'] !== $hiring_academy->name) {
            $data['slug'] = HiringAcademy::generateUniqueSlug($data['name']);
        }
        $hiring_academy->update($data);

        return redirect()->route('admin.hiring-academies.show', $hiring_academy)->with('success', __('admin.hiring_academy_updated'));
    }

    public function destroy(HiringAcademy $hiring_academy)
    {
        if ($hiring_academy->opportunities()->exists()) {
            return back()->with('error', __('admin.hiring_academy_has_opportunities'));
        }
        $hiring_academy->delete();

        return redirect()->route('admin.hiring-academies.index')->with('success', __('admin.hiring_academy_deleted'));
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:180'],
            'legal_name' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:120'],
            'address' => ['nullable', 'string', 'max:500'],
            'contact_name' => ['nullable', 'string', 'max:120'],
            'contact_email' => ['nullable', 'email', 'max:190'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
            'website' => ['nullable', 'string', 'max:255'],
            'tax_id' => ['nullable', 'string', 'max:80'],
            'status' => ['required', Rule::in(array_keys(HiringAcademy::statusLabels()))],
            'commercial_notes' => ['nullable', 'string', 'max:20000'],
            'internal_notes' => ['nullable', 'string', 'max:20000'],
        ]);
    }
}
