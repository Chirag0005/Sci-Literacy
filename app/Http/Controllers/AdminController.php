<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Services\GeminiService;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function generateQuestion(Request $request, GeminiService $aiService)
    {
        $questionData = $aiService->generateQuestion();

        if ($questionData) {
            return response()->json([
                'success' => true,
                'data' => $questionData
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to generate question from AI.'
        ], 500);
    }
    public function index()
    {
        $questionsData = \App\Services\MongoService::execute('find', 'questions');
        $questions = collect($questionsData)->map(function ($q) {
            $question = new Question();
            $question->forceFill($q);
            $question->id = $q['_id'];
            return $question;
        });
        return view('admin.questions', compact('questions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'question_text' => 'required|string',
            'category' => 'required|string',
            'option_a' => 'required|string',
            'option_b' => 'required|string',
            'option_c' => 'required|string',
            'option_d' => 'required|string',
            'correct_option' => 'required|in:A,B,C,D',
            'explanation' => 'required|string',
        ]);

        \App\Services\MongoService::execute('insert', 'questions', [], $validated);
        return back()->with('success', 'Question added successfully.');
    }

    public function destroy($id)
    {
        \App\Services\MongoService::execute('delete', 'questions', ['_id' => $id]);
        return back()->with('success', 'Question deleted successfully.');
    }
}
