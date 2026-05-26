<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\Result;
use Illuminate\Http\Request;

class ApiController extends Controller
{
    public function getQuestions()
    {
        $questionsData = \App\Services\MongoService::execute('find', 'questions');
        $questions = collect($questionsData)->map(function ($q) {
            $question = new Question();
            $question->forceFill($q);
            $question->id = $q['_id'];
            return $question;
        });

        return response()->json([
            'status' => 'success',
            'data' => $questions
        ]);
    }

    public function getResults()
    {
        $resultsData = \App\Services\MongoService::execute('find', 'results', [], [], ['score' => -1]);
        $results = collect($resultsData)->map(function ($r) {
            $res = new Result();
            $res->forceFill($r);
            $res->id = $r['_id'];
            return $res;
        });

        return response()->json([
            'status' => 'success',
            'data' => $results
        ]);
    }
}
