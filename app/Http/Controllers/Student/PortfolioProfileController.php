<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class PortfolioProfileController extends Controller
{
    private function ensureTeacherProfileSubscription(User $user): void
    {
        abort_unless($user->hasSubscriptionFeature('teacher_profile'), 403, 'ميزة البروفايل التسويقي للمعلم غير مفعّلة في اشتراكك. يمكنك الترقية من صفحة التسعير.');
    }

    public function edit()
    {
        $user = auth()->user();
        $this->ensureTeacherProfileSubscription($user);
        return view('student.portfolio.profile', compact('user'));
    }

    public function update(Request $request)
    {
        $user = auth()->user();
        $this->ensureTeacherProfileSubscription($user);

        $validated = $request->validate([
            'portfolio_headline' => 'nullable|string|max:120',
            'portfolio_about' => 'nullable|string|max:3000',
            'portfolio_skills' => 'nullable|string|max:2000',
            'portfolio_intro_video_url' => 'nullable|url|max:500',

            'linkedin' => 'nullable|url|max:500',
            'twitter' => 'nullable|url|max:500',
            'youtube' => 'nullable|url|max:500',
            'facebook' => 'nullable|url|max:500',
            'website' => 'nullable|url|max:500',
        ], [
            'portfolio_intro_video_url.url' => 'رابط فيديو التعريف يجب أن يكون رابطاً صحيحاً',
        ]);

        $social = is_array($user->portfolio_social_links) ? $user->portfolio_social_links : [];
        foreach (['linkedin', 'twitter', 'youtube', 'facebook', 'website'] as $k) {
            $v = trim((string) ($validated[$k] ?? ''));
            $social[$k] = $v !== '' ? $v : null;
            unset($validated[$k]);
        }

        $user->update([
            'portfolio_headline' => $validated['portfolio_headline'] ?? null,
            'portfolio_about' => $validated['portfolio_about'] ?? null,
            'portfolio_skills' => $validated['portfolio_skills'] ?? null,
            'portfolio_intro_video_url' => $validated['portfolio_intro_video_url'] ?? null,
            'portfolio_social_links' => $social,
        ]);

        return redirect()->route('student.portfolio.profile.edit')->with('success', 'تم حفظ بيانات الملف التعريفي.');
    }
}

