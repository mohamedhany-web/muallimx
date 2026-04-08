<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\UserProfileImageStorage;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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
            'profile_image' => [
                'nullable',
                'image',
                'max:'.config('upload_limits.max_upload_kb'),
                Rule::requiredIf(function () use ($user, $request) {
                    if ($request->boolean('remove_profile_image')) {
                        return false;
                    }

                    return empty($user->profile_image);
                }),
            ],
            'remove_profile_image' => 'nullable|boolean',
        ], [
            'portfolio_intro_video_url.url' => 'رابط فيديو التعريف يجب أن يكون رابطاً صحيحاً',
            'profile_image.image' => 'صورة الملف يجب أن تكون ملف صورة.',
            'profile_image.max' => 'حجم الصورة يتجاوز الحد المسموح.',
            'profile_image.required' => __('student.portfolio_marketing.profile_image_required'),
            'profile_image.required_if' => __('student.portfolio_marketing.profile_image_required'),
        ]);

        $payload = [
            'portfolio_headline' => $validated['portfolio_headline'] ?? null,
            'portfolio_about' => $validated['portfolio_about'] ?? null,
            'portfolio_skills' => $validated['portfolio_skills'] ?? null,
            'portfolio_intro_video_url' => $validated['portfolio_intro_video_url'] ?? null,
            'portfolio_social_links' => null,
        ];

        if ($request->boolean('remove_profile_image')) {
            UserProfileImageStorage::delete($user->profile_image);
            $payload['profile_image'] = null;
        } elseif ($request->hasFile('profile_image')) {
            UserProfileImageStorage::delete($user->profile_image);
            $payload['profile_image'] = UserProfileImageStorage::store($request->file('profile_image'));
        }

        if (
            ($user->portfolio_profile_status === null || $user->portfolio_profile_status === User::PORTFOLIO_PROFILE_APPROVED)
            && empty($user->portfolio_marketing_published)
        ) {
            $payload['portfolio_marketing_published'] = $user->snapshotPortfolioMarketingForPublish();
        }

        $payload['portfolio_profile_status'] = User::PORTFOLIO_PROFILE_PENDING;
        $payload['portfolio_profile_submitted_at'] = now();
        $payload['portfolio_profile_rejected_reason'] = null;

        $user->update($payload);

        return redirect()->route('student.portfolio.profile.edit')->with('success', __('student.portfolio_marketing.profile_saved_pending_review'));
    }
}

