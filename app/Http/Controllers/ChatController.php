<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\GeminiService;

class ChatController extends Controller
{
    public function sendMessage(Request $request, GeminiService $ai)
    {
        $request->validate([
            'message' => 'required|string|max:500'
        ]);

        $reply = $ai->chatWithTutor($request->message);

        // Store chat log in MongoDB
        try {
            \App\Services\MongoService::execute('insert', 'chat_logs', [], [
                'user_id' => auth()->id(),
                'message' => $request->message,
                'reply' => $reply,
                'created_at' => now()->toIso8601String()
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("ChatController Failed to log chat: " . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'reply' => $reply
        ]);
    }

    public function getChatHistory()
    {
        try {
            $chatData = \App\Services\MongoService::execute(
                'find',
                'chat_logs',
                ['user_id' => auth()->id()],
                [],
                ['created_at' => 1], // Chronological order
                20
            );

            return response()->json([
                'success' => true,
                'history' => $chatData ?: []
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("ChatController Failed to fetch chat history: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'history' => []
            ], 500);
        }
    }

    public function explain(Request $request, GeminiService $ai)
    {
        $request->validate([
            'question' => 'required|string',
            'user_answer' => 'required|string',
            'correct_answer' => 'required|string',
        ]);

        $explanation = $ai->explainAnswer($request->question, $request->user_answer, $request->correct_answer);

        return response()->json([
            'success' => true,
            'explanation' => $explanation
        ]);
    }
}
