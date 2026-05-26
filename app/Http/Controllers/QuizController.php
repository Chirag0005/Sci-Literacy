<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\Result;
use App\Services\GeminiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class QuizController extends Controller
{
    public function index()
    {
        return view('home');
    }

    public function showQuiz(Request $request)
    {
        // Fetch all questions from MongoDB
        $questionsData = \App\Services\MongoService::execute('find', 'questions');

        // Trigger database seeding if empty
        if (empty($questionsData)) {
            \Illuminate\Support\Facades\Artisan::call('db:seed');
            $questionsData = \App\Services\MongoService::execute('find', 'questions');
        }

        // Shuffle questions in PHP and take 5
        shuffle($questionsData);
        $selectedData = array_slice($questionsData, 0, 5);

        $questions = collect($selectedData)->map(function ($q) {
            $question = new Question();
            $question->forceFill($q);
            $question->id = $q['_id'];
            return $question;
        });

        $request->session()->put('quiz_questions', $questions->pluck('id')->toArray());

        return view('quiz', compact('questions'));
    }

    public function submitQuiz(Request $request, GeminiService $aiService)
    {
        if (!$request->session()->has('quiz_questions')) {
            return redirect()->route('dashboard');
        }

        $questionIds = $request->session()->get('quiz_questions');

        // Fetch matching questions from MongoDB
        $questionsData = \App\Services\MongoService::execute('find', 'questions', [
            '_id' => ['$in' => $questionIds]
        ]);

        $questions = collect($questionsData)->map(function ($q) {
            $question = new Question();
            $question->forceFill($q);
            $question->id = $q['_id'];
            return $question;
        });

        // Maintain the exact order generated in session
        $questions = $questions->sortBy(function ($q) use ($questionIds) {
            return array_search($q->id, $questionIds);
        })->values();

        $rules = [];
        $messages = [];
        foreach ($questions as $q) {
            $rules['q_' . $q->id] = 'required|in:A,B,C,D';
            $messages['q_' . $q->id . '.required'] = 'Please answer question about "' . substr($q->question_text, 0, 20) . '..."';
        }

        $request->validate($rules, $messages);

        $score = 0;
        $incorrectAnswersData = [];
        $summary = [];

        foreach ($questions as $q) {
            $userAnswer = $request->input('q_' . $q->id);
            $isCorrect = $userAnswer === $q->correct_option;

            if ($isCorrect) {
                $score++;
            } else {
                $optionMap = [
                    'A' => $q->option_a, 'B' => $q->option_b,
                    'C' => $q->option_c, 'D' => $q->option_d
                ];
                $incorrectAnswersData[] = [
                    'question' => $q->question_text,
                    'user_answer' => $optionMap[$userAnswer] ?? $userAnswer,
                    'correct_answer' => $optionMap[$q->correct_option] ?? $q->correct_option
                ];
            }

            $summary[] = [
                'question' => $q,
                'user_answer' => $userAnswer,
                'is_correct' => $isCorrect
            ];
        }

        $user = auth()->user();

        // Generate AI Feedback
        $aiFeedback = $aiService->generateFeedback($user->name, $score, $questions->count(), $incorrectAnswersData);

        // Store result in MongoDB results collection
        $resultData = [
            'user_id' => $user->id,
            'score' => $score,
            'total_questions' => $questions->count(),
            'ai_feedback' => $aiFeedback,
            'created_at' => now()->toIso8601String()
        ];

        $insertResult = \App\Services\MongoService::execute('insert', 'results', [], $resultData);
        $resultId = $insertResult['insertedId'];

        // Award dynamic XP
        $xpGained = $score * 10;
        $newXP = ($user->xp ?? 0) + $xpGained;

        \App\Services\MongoService::execute('update', 'users', ['_id' => $user->id], [
            'xp' => $newXP,
            'updated_at' => now()->toIso8601String()
        ]);

        // Re-hydrate current authenticated user XP
        $user->xp = $newXP;

        $request->session()->forget('quiz_questions');
        $request->session()->put('quiz_summary_' . $resultId, $summary);

        return redirect()->route('results.show', $resultId)->with('success', 'Quiz submitted successfully!');
    }

    public function showResults(Request $request, $id)
    {
        $r = \App\Services\MongoService::execute('findOne', 'results', ['_id' => $id]);
        if (!$r) {
            abort(404, 'Result not found.');
        }

        $result = new Result();
        $result->forceFill($r);
        $result->id = $r['_id'];

        // Load user relation for the primary result
        $userData = \App\Services\MongoService::execute('findOne', 'users', ['_id' => $result->user_id]);
        $user = new \App\Models\User();
        if ($userData) {
            $user->forceFill($userData);
            $user->id = $userData['_id'];
        } else {
            $user->name = 'Unknown Explorer';
        }
        $result->setRelation('user', $user);

        // Fetch top 5 high scores from MongoDB results collection
        $allResultsData = \App\Services\MongoService::execute('find', 'results', [], [], ['score' => -1], 5);
        $allResults = collect($allResultsData)->map(function ($resData) {
            $userData = \App\Services\MongoService::execute('findOne', 'users', ['_id' => $resData['user_id']]);
            $res = new Result();
            $res->forceFill($resData);
            $res->id = $resData['_id'];

            $user = new \App\Models\User();
            if ($userData) {
                $user->forceFill($userData);
                $user->id = $userData['_id'];
            } else {
                $user->name = 'Unknown Explorer';
            }
            $res->setRelation('user', $user);
            return $res;
        });

        $summary = $request->session()->get('quiz_summary_' . $result->id, []);

        return view('results', compact('result', 'allResults', 'summary'));
    }
}
