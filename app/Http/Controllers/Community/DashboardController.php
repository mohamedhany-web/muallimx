<?php

namespace App\Http\Controllers\Community;

use App\Http\Controllers\Controller;
use App\Models\CommunityCompetition;
use App\Models\CommunityDataset;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

/**
 * لوحة تحكم مجتمع البيانات — كروت وإحصائيات تعبّر عن المجتمع.
 */
class DashboardController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();

        $stats = [
            'competitions_count' => CommunityCompetition::active()->count(),
            'datasets_count' => CommunityDataset::active()->count(),
        ];

        $recentCompetitions = CommunityCompetition::active()->ordered()->take(3)->get();
        $recentDatasets = CommunityDataset::active()->ordered()->take(3)->get();

        return view('community.dashboard.index', [
            'user' => $user,
            'stats' => $stats,
            'recentCompetitions' => $recentCompetitions,
            'recentDatasets' => $recentDatasets,
        ]);
    }
}
