<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\LandingPage;
use App\Services\LandingPageCtaResolver;
use App\Services\YouTubeVideoService;
use Illuminate\View\View;

class LandingPageController extends Controller
{
    public function show(LandingPage $landingPage): View
    {
        if (! $landingPage->isPublishedNow()) {
            abort(404);
        }

        $sections = collect($landingPage->orderedSections())->map(function (array $section) use ($landingPage) {
            if (in_array($section['type'] ?? '', ['hero', 'cta'], true)) {
                $section['resolved_buttons'] = LandingPageCtaResolver::resolveMany(
                    $section['buttons'] ?? [],
                    $landingPage->utm_source,
                    $landingPage->utm_campaign
                );
            }

            if (($section['type'] ?? '') === 'video' && ! empty($section['youtube_id'])) {
                $section['embed_url'] = YouTubeVideoService::embedUrl(
                    $section['youtube_id'],
                    ['origin' => request()->getSchemeAndHttpHost()]
                );
            }

            return $section;
        })->all();

        return view('public.landing-pages.show', compact('landingPage', 'sections'));
    }
}
