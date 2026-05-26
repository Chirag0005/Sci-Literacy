<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\GeminiService;
use Illuminate\Support\Facades\Cache;

class MythController extends Controller
{
    public function index(GeminiService $ai)
    {
        // Cache myths for 12 hours (12 * 60 * 60 seconds)
        $myths = Cache::remember('science_myths', 12 * 60 * 60, function () use ($ai) {
            return $ai->generateMyths();
        });

        return view('myths', compact('myths'));
    }
}
