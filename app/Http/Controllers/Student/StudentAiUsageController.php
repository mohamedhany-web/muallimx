<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\Student\StoreStudentSavedAiGameRequest;
use App\Models\StudentSavedAiGame;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentAiUsageController extends Controller
{
    public function index(Request $request): View
    {
        $games = StudentSavedAiGame::query()
            ->where('user_id', $request->user()->id)
            ->orderByDesc('updated_at')
            ->paginate(20);

        return view('student.ai-usages.index', [
            'games' => $games,
        ]);
    }

    public function store(StoreStudentSavedAiGameRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        StudentSavedAiGame::query()->updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'storage_path' => $validated['storage_path'],
            ],
            [
                'title' => $validated['title'] ?? null,
                'question_type' => 'educational_games',
            ]
        );

        return redirect()
            ->route('student.ai-usages.index')
            ->with('success', __('student.ai_usages.saved_ok'));
    }

    public function destroy(Request $request, int $game): RedirectResponse
    {
        $row = StudentSavedAiGame::query()
            ->where('user_id', $request->user()->id)
            ->whereKey($game)
            ->firstOrFail();
        $row->delete();

        return redirect()
            ->route('student.ai-usages.index')
            ->with('success', __('student.ai_usages.deleted_ok'));
    }
}
