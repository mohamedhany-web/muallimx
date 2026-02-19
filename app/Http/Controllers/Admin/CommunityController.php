<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CommunityCompetition;
use App\Models\CommunityDataset;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

/**
 * إدارة مجتمع البيانات والذكاء الاصطناعي — للإدارة العليا فقط.
 * (لوحة المجتمع، مسابقات، مجموعات بيانات، تقديمات، مناقشات، إعدادات)
 */
class CommunityController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:super_admin');
    }

    public function dashboard(): View
    {
        $stats = [
            'competitions_count' => CommunityCompetition::count(),
            'competitions_active' => CommunityCompetition::active()->count(),
            'datasets_count' => CommunityDataset::count(),
            'datasets_active' => CommunityDataset::active()->count(),
        ];
        $recentCompetitions = CommunityCompetition::ordered()->take(4)->get();
        $recentDatasets = CommunityDataset::ordered()->take(4)->get();

        return view('admin.community.dashboard', [
            'stats' => $stats,
            'recentCompetitions' => $recentCompetitions,
            'recentDatasets' => $recentDatasets,
        ]);
    }

    public function competitions(): View
    {
        return view('admin.community.coming-soon', ['section' => 'competitions']);
    }

    public function datasets(): View
    {
        return view('admin.community.coming-soon', ['section' => 'datasets']);
    }

    public function submissions(): View
    {
        return view('admin.community.coming-soon', ['section' => 'submissions']);
    }

    public function discussions(): View
    {
        return view('admin.community.coming-soon', ['section' => 'discussions']);
    }

    public function settings(): View
    {
        return view('admin.community.coming-soon', ['section' => 'settings']);
    }
}
