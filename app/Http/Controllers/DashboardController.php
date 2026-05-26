<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use App\Services\GeminiService;

class DashboardController extends Controller
{
    public function index(GeminiService $aiService)
    {
        $user = Auth::user();
        
        // Fetch results from MongoDB
        $resultsData = \App\Services\MongoService::execute('find', 'results', ['user_id' => $user->id], [], ['created_at' => -1]);
        $results = collect($resultsData)->map(function ($r) {
            $res = new \App\Models\Result();
            $res->forceFill($r);
            $res->id = $r['_id'];
            return $res;
        });
        
        $totalScore = $results->sum('score');
        $totalPossible = $results->sum('total_questions');
        $average = $totalPossible > 0 ? round(($totalScore / $totalPossible) * 100) : 0;
        
        $rank = 'Novice';
        if ($totalScore > 50) $rank = 'Visionary';
        elseif ($totalScore > 30) $rank = 'Scientist';
        elseif ($totalScore > 10) $rank = 'Scholar';

        // Cache the daily insight for 24 hours
        $dailyInsight = Cache::remember('daily_science_insight', 60 * 24, function () use ($aiService) {
            return $aiService->generateDailyInsight();
        });

        return view('dashboard', compact('user', 'results', 'totalScore', 'average', 'rank', 'dailyInsight'));
    }

    public function leaderboard()
    {
        // For global leaderboard, we aggregate scores per user
        $users = \App\Services\MongoService::execute('find', 'users');
        $leaders = collect($users)->map(function ($u) {
            $userResults = \App\Services\MongoService::execute('find', 'results', ['user_id' => $u['_id']]);
            $sumScore = collect($userResults)->sum('score');
            
            $user = new \App\Models\User();
            $user->forceFill($u);
            $user->id = $u['_id'];
            $user->results_sum_score = $sumScore;
            return $user;
        })->filter(function ($u) {
            return $u->results_sum_score > 0;
        })->sortByDesc('results_sum_score')->take(10)->values();

        return view('leaderboard', compact('leaders'));
    }
}
