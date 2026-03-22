<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademyOpportunity;
use App\Models\InstructorProfile;
use App\Models\RecruitmentTeacherPresentation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RecruitmentDeskController extends Controller
{
    public function show(AcademyOpportunity $academy_opportunity)
    {
        $academy_opportunity->load(['hiringAcademy', 'creator']);
        $presentations = RecruitmentTeacherPresentation::query()
            ->where('academy_opportunity_id', $academy_opportunity->id)
            ->with(['user.instructorProfile', 'creator'])
            ->latest()
            ->get();

        $statusCounts = $presentations->groupBy('status')->map->count();

        return view('admin.recruitment-desk.show', [
            'opportunity' => $academy_opportunity,
            'presentations' => $presentations,
            'statusCounts' => $statusCounts,
        ]);
    }

    public function searchInstructors(Request $request, AcademyOpportunity $academy_opportunity)
    {
        $q = trim((string) $request->get('q', ''));
        if (mb_strlen($q) < 2) {
            return response()->json(['data' => []]);
        }

        $users = User::query()
            ->where('role', 'instructor')
            ->where('is_active', true)
            ->where(function ($qq) use ($q) {
                $qq->where('name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%")
                    ->orWhere('phone', 'like', "%{$q}%");
            })
            ->whereHas('instructorProfile', fn ($p) => $p->where('status', InstructorProfile::STATUS_APPROVED))
            ->with('instructorProfile:id,user_id,headline')
            ->orderBy('name')
            ->limit(25)
            ->get(['id', 'name', 'email']);

        return response()->json([
            'data' => $users->map(fn (User $u) => [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'headline' => $u->instructorProfile->headline ?? null,
            ]),
        ]);
    }

    public function storePresentation(Request $request, AcademyOpportunity $academy_opportunity)
    {
        $data = $request->validate([
            'user_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where(fn ($q) => $q->where('role', 'instructor')),
            ],
            'curated_public_profile' => ['required', 'string', 'min:20', 'max:20000'],
            'hide_identity' => ['nullable', 'boolean'],
            'internal_notes' => ['nullable', 'string', 'max:20000'],
            'status' => ['nullable', Rule::in(array_keys(RecruitmentTeacherPresentation::statusLabels()))],
        ]);

        $user = User::findOrFail($data['user_id']);
        if (! InstructorProfile::approved()->where('user_id', $user->id)->exists()) {
            return back()->with('error', __('admin.recruitment_instructor_not_approved'))->withInput();
        }

        if (RecruitmentTeacherPresentation::where('academy_opportunity_id', $academy_opportunity->id)->where('user_id', $user->id)->exists()) {
            return back()->with('error', __('admin.recruitment_duplicate_teacher'))->withInput();
        }

        $status = $data['status'] ?? RecruitmentTeacherPresentation::STATUS_DRAFT;
        RecruitmentTeacherPresentation::create([
            'academy_opportunity_id' => $academy_opportunity->id,
            'user_id' => $user->id,
            'curated_public_profile' => $data['curated_public_profile'],
            'hide_identity' => (bool) ($data['hide_identity'] ?? false),
            'internal_notes' => $data['internal_notes'] ?? null,
            'status' => $status,
            'created_by' => auth()->id(),
            'shared_with_academy_at' => $status === RecruitmentTeacherPresentation::STATUS_SHARED ? now() : null,
        ]);

        return back()->with('success', __('admin.recruitment_presentation_created'));
    }

    public function updatePresentation(Request $request, AcademyOpportunity $academy_opportunity, RecruitmentTeacherPresentation $presentation)
    {
        $this->assertPresentation($academy_opportunity, $presentation);

        $data = $request->validate([
            'curated_public_profile' => ['required', 'string', 'min:10', 'max:20000'],
            'hide_identity' => ['nullable', 'boolean'],
            'internal_notes' => ['nullable', 'string', 'max:20000'],
            'academy_feedback' => ['nullable', 'string', 'max:20000'],
            'status' => ['required', Rule::in(array_keys(RecruitmentTeacherPresentation::statusLabels()))],
        ]);

        $update = [
            'curated_public_profile' => $data['curated_public_profile'],
            'hide_identity' => (bool) ($data['hide_identity'] ?? false),
            'internal_notes' => $data['internal_notes'] ?? null,
            'academy_feedback' => $data['academy_feedback'] ?? null,
            'status' => $data['status'],
        ];

        if ($data['status'] === RecruitmentTeacherPresentation::STATUS_SHARED && ! $presentation->shared_with_academy_at) {
            $update['shared_with_academy_at'] = now();
        }
        if (in_array($data['status'], [RecruitmentTeacherPresentation::STATUS_INTERESTED, RecruitmentTeacherPresentation::STATUS_DECLINED], true)) {
            $update['academy_responded_at'] = $presentation->academy_responded_at ?? now();
        }

        $presentation->update($update);

        return back()->with('success', __('admin.recruitment_presentation_updated'));
    }

    public function destroyPresentation(AcademyOpportunity $academy_opportunity, RecruitmentTeacherPresentation $presentation)
    {
        $this->assertPresentation($academy_opportunity, $presentation);
        if (! in_array($presentation->status, [RecruitmentTeacherPresentation::STATUS_DRAFT, RecruitmentTeacherPresentation::STATUS_WITHDRAWN], true)) {
            return back()->with('error', __('admin.recruitment_presentation_delete_denied'));
        }
        $presentation->delete();

        return back()->with('success', __('admin.recruitment_presentation_deleted'));
    }

    public function printForAcademy(AcademyOpportunity $academy_opportunity, RecruitmentTeacherPresentation $presentation)
    {
        $this->assertPresentation($academy_opportunity, $presentation);
        $presentation->load(['user.instructorProfile', 'opportunity.hiringAcademy']);

        return view('admin.recruitment-desk.print-packet', [
            'opportunity' => $academy_opportunity,
            'presentation' => $presentation,
        ]);
    }

    private function assertPresentation(AcademyOpportunity $opportunity, RecruitmentTeacherPresentation $presentation): void
    {
        abort_unless((int) $presentation->academy_opportunity_id === (int) $opportunity->id, 404);
    }
}
