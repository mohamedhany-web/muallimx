<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LandingPage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LandingPageController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:manage.landing-pages');
    }

    public function index(Request $request)
    {
        $query = LandingPage::query()->orderByDesc('updated_at');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('title', 'like', '%'.$s.'%')
                    ->orWhere('slug', 'like', '%'.$s.'%')
                    ->orWhere('headline', 'like', '%'.$s.'%')
                    ->orWhere('utm_campaign', 'like', '%'.$s.'%');
            });
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $pages = $query->paginate(20)->withQueryString();

        return view('admin.landing-pages.index', compact('pages'));
    }

    public function create(Request $request)
    {
        $useTemplate = $request->boolean('template');
        $sections = $useTemplate ? LandingPage::adTemplateSections() : [];

        return view('admin.landing-pages.create', [
            'sectionsJson' => $sections,
            'useTemplate' => $useTemplate,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validatePage($request);
        $sections = $this->parseAndNormalizeSections($request);

        $slug = $this->resolveUniqueSlug(
            $validated['slug'] ?? null,
            $validated['title'],
            null
        );

        $ogPath = null;
        if ($request->hasFile('og_image')) {
            $ogPath = $request->file('og_image')->store('landing-pages', 'public');
        }

        LandingPage::create([
            'title' => $validated['title'],
            'slug' => $slug,
            'meta_title' => $validated['meta_title'] ?? null,
            'meta_description' => $validated['meta_description'] ?? null,
            'og_image_path' => $ogPath,
            'headline' => $validated['headline'] ?? null,
            'subheadline' => $validated['subheadline'] ?? null,
            'sections' => $sections,
            'utm_source' => $validated['utm_source'] ?? null,
            'utm_campaign' => $validated['utm_campaign'] ?? null,
            'is_active' => $request->boolean('is_active'),
            'starts_at' => $validated['starts_at'] ?? null,
            'ends_at' => $validated['ends_at'] ?? null,
        ]);

        return redirect()->route('admin.landing-pages.index')
            ->with('success', 'تم إنشاء صفحة الهبوط بنجاح');
    }

    public function edit(LandingPage $landingPage)
    {
        return view('admin.landing-pages.edit', [
            'landingPage' => $landingPage,
            'sectionsJson' => $landingPage->orderedSections(),
        ]);
    }

    public function update(Request $request, LandingPage $landingPage)
    {
        $validated = $this->validatePage($request);
        $sections = $this->parseAndNormalizeSections($request);

        $slug = $this->resolveUniqueSlug(
            $validated['slug'] ?? null,
            $validated['title'],
            $landingPage->id
        );

        $ogPath = $landingPage->og_image_path;
        if ($request->hasFile('og_image')) {
            if ($ogPath) {
                Storage::disk('public')->delete($ogPath);
            }
            $ogPath = $request->file('og_image')->store('landing-pages', 'public');
        } elseif ($request->boolean('remove_og_image')) {
            if ($ogPath) {
                Storage::disk('public')->delete($ogPath);
            }
            $ogPath = null;
        }

        $landingPage->update([
            'title' => $validated['title'],
            'slug' => $slug,
            'meta_title' => $validated['meta_title'] ?? null,
            'meta_description' => $validated['meta_description'] ?? null,
            'og_image_path' => $ogPath,
            'headline' => $validated['headline'] ?? null,
            'subheadline' => $validated['subheadline'] ?? null,
            'sections' => $sections,
            'utm_source' => $validated['utm_source'] ?? null,
            'utm_campaign' => $validated['utm_campaign'] ?? null,
            'is_active' => $request->boolean('is_active'),
            'starts_at' => $validated['starts_at'] ?? null,
            'ends_at' => $validated['ends_at'] ?? null,
        ]);

        return redirect()->route('admin.landing-pages.index')
            ->with('success', 'تم تحديث صفحة الهبوط بنجاح');
    }

    public function destroy(LandingPage $landingPage)
    {
        $landingPage->delete();

        return redirect()->route('admin.landing-pages.index')
            ->with('success', 'تم حذف صفحة الهبوط');
    }

    /**
     * @return array<string, mixed>
     */
    private function validatePage(Request $request): array
    {
        if (! $request->filled('slug')) {
            $request->merge(['slug' => null]);
        }

        return $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'headline' => 'nullable|string|max:255',
            'subheadline' => 'nullable|string|max:2000',
            'utm_source' => 'nullable|string|max:100',
            'utm_campaign' => 'nullable|string|max:100',
            'is_active' => 'boolean',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'og_image' => 'nullable|image|mimes:jpeg,jpg,png,webp,gif|max:5120',
            'remove_og_image' => 'boolean',
            'sections_json' => 'nullable|string',
        ], [
            'title.required' => 'عنوان الصفحة مطلوب',
            'slug.regex' => 'الرابط المختصر يقبل أحرف إنجليزية صغيرة وأرقام وشرطة فقط',
            'ends_at.after_or_equal' => 'تاريخ الانتهاء يجب أن يكون بعد أو يساوي تاريخ البداية',
        ]);
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function parseAndNormalizeSections(Request $request): array
    {
        $raw = $request->input('sections_json', '[]');
        $decoded = json_decode((string) $raw, true);

        if (! is_array($decoded)) {
            throw ValidationException::withMessages([
                'sections_json' => 'تنسيق الأقسام غير صالح. أعد تحميل الصفحة وحاول مرة أخرى.',
            ]);
        }

        $sections = LandingPage::normalizeSections($decoded);

        foreach ($sections as $i => $section) {
            if (($section['type'] ?? '') === 'video') {
                $url = trim((string) ($section['youtube_url'] ?? ''));
                if ($url !== '' && empty($section['youtube_id'])) {
                    throw ValidationException::withMessages([
                        'sections_json' => 'قسم الفيديو رقم '.($i + 1).': رابط يوتيوب غير صالح.',
                    ]);
                }
            }
        }

        return $sections;
    }

    private function resolveUniqueSlug(?string $slugInput, string $title, ?int $exceptId): string
    {
        $base = $slugInput !== null && $slugInput !== ''
            ? Str::slug($slugInput, '-', 'en')
            : Str::slug($title, '-', 'en');

        if ($base === '') {
            $base = 'lp';
        }

        $slug = $base;
        $n = 1;
        while (LandingPage::where('slug', $slug)
            ->when($exceptId, fn ($q) => $q->where('id', '!=', $exceptId))
            ->exists()) {
            $slug = $base.'-'.$n++;
        }

        return $slug;
    }
}
