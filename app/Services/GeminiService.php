<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    protected $apiKey;
    protected $apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent';

    public function __construct()
    {
        $this->apiKey = env('GEMINI_API_KEY');
    }

    public function generateFeedback($userName, $score, $total, $incorrectAnswersData)
    {
        if (empty($this->apiKey)) {
            return null; // Fallback to built-in explanations if no API key
        }

        $prompt = "You are a friendly, encouraging science tutor evaluating {$userName}'s quiz performance.\n";
        $prompt .= "They scored {$score} out of {$total} on a scientific temper quiz.\n";
        
        if (empty($incorrectAnswersData)) {
            $prompt .= "They got a perfect score! Give them a short, enthusiastic congratulatory message praising their excellent scientific literacy.";
        } else {
            $prompt .= "Here are the questions they got wrong, along with their answer and the correct answer:\n";
            foreach ($incorrectAnswersData as $data) {
                $prompt .= "- Question: {$data['question']}\n";
                $prompt .= "  Their Answer: {$data['user_answer']}\n";
                $prompt .= "  Correct Answer: {$data['correct_answer']}\n";
            }
            $prompt .= "\nPlease provide a short, personalized paragraph of feedback. Don't list the questions again. Instead, gently explain the common misconceptions they seem to have based on these specific wrong answers, and encourage them to think more critically like a scientist. Keep it under 150 words and use a supportive tone.";
        }

        try {
            $response = Http::post($this->apiUrl . '?key=' . $this->apiKey, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ]
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
            } else {
                Log::error("Gemini API Error: " . $response->body());
                return null;
            }
        } catch (\Exception $e) {
            Log::error("Gemini API Exception: " . $e->getMessage());
            return null;
        }
    }

    public function generateQuestion()
    {
        if (empty($this->apiKey)) return null;

        $prompt = "You are a scientific quiz generator. Generate a multiple-choice question to test scientific temper and literacy.\n";
        $prompt .= "The response MUST be ONLY valid JSON with no markdown formatting or backticks. Format:\n";
        $prompt .= "{\n";
        $prompt .= '  "question_text": "The question string",'."\n";
        $prompt .= '  "category": "String (e.g. Physics, Biology, Chemistry)",'."\n";
        $prompt .= '  "option_a": "Option A text",'."\n";
        $prompt .= '  "option_b": "Option B text",'."\n";
        $prompt .= '  "option_c": "Option C text",'."\n";
        $prompt .= '  "option_d": "Option D text",'."\n";
        $prompt .= '  "correct_option": "A or B or C or D",'."\n";
        $prompt .= '  "explanation": "Short explanation of why the correct option is true"'."\n";
        $prompt .= "}";

        try {
            $response = Http::post($this->apiUrl . '?key=' . $this->apiKey, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ]
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
                // Extract JSON object if wrapped in conversational text or markdown
                if (preg_match('/\{.*\}/s', $text, $matches)) {
                    $jsonText = $matches[0];
                } else {
                    $jsonText = $text;
                }
                $jsonText = str_replace(['```json', '```'], '', trim($jsonText));
                return json_decode($jsonText, true);
            }
        } catch (\Exception $e) {
            Log::error("Gemini API Exception (generateQuestion): " . $e->getMessage());
        }

        return null;
    }

    public function generateDailyInsight()
    {
        if (empty($this->apiKey)) return "Science is the systematic enterprise that builds and organizes knowledge.";

        $prompt = "Provide a short, fascinating science fact or debunk a common scientific myth. Make it engaging, surprising, and easy to understand for the general public. Keep it under 50 words.";

        try {
            $response = Http::post($this->apiUrl . '?key=' . $this->apiKey, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ]
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['candidates'][0]['content']['parts'][0]['text'] ?? "Science is the systematic enterprise that builds and organizes knowledge.";
            }
        } catch (\Exception $e) {
            Log::error("Gemini API Exception (generateDailyInsight): " . $e->getMessage());
        }

        return "Science is the systematic enterprise that builds and organizes knowledge.";
    }

    public function chatWithTutor($message)
    {
        if (empty($this->apiKey)) return "API key missing.";

        $prompt = "You are a friendly, highly intelligent science tutor on a Science Literacy platform. " .
                  "A student asks: \"{$message}\". " .
                  "Provide a helpful, encouraging, and scientifically accurate response. Keep it concise (under 100 words).";

        try {
            $response = Http::post($this->apiUrl . '?key=' . $this->apiKey, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ]
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['candidates'][0]['content']['parts'][0]['text'] ?? "I'm sorry, I couldn't process that.";
            }
        } catch (\Exception $e) {
            Log::error("Gemini API Exception (chatWithTutor): " . $e->getMessage());
        }

        return "I'm having trouble connecting right now.";
    }

    public function explainAnswer($questionText, $userAnswer, $correctAnswer)
    {
        if (empty($this->apiKey)) return "API key missing.";

        $prompt = "A student answered a science question incorrectly.\n" .
                  "Question: \"{$questionText}\"\n" .
                  "Their wrong answer: \"{$userAnswer}\"\n" .
                  "Correct answer: \"{$correctAnswer}\"\n" .
                  "Explain clearly and simply WHY their answer is wrong, and WHY the correct answer is right. Keep it under 100 words.";

        try {
            $response = Http::post($this->apiUrl . '?key=' . $this->apiKey, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ]
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['candidates'][0]['content']['parts'][0]['text'] ?? "Unable to generate explanation.";
            }
        } catch (\Exception $e) {
            Log::error("Gemini API Exception (explainAnswer): " . $e->getMessage());
        }

        return "Explanation unavailable.";
    }

    public function generateMyths()
    {
        if (empty($this->apiKey)) return [];

        $prompt = "Generate 5 common scientific myths and debunk them.\n" .
                  "Format the response strictly as valid JSON like this:\n" .
                  "[\n" .
                  "  {\"myth\": \"The myth text\", \"fact\": \"The actual scientific fact debunking it\"}\n" .
                  "]";

        try {
            $response = Http::post($this->apiUrl . '?key=' . $this->apiKey, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ]
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
                // Extract JSON array if wrapped in conversational text or markdown
                if (preg_match('/\[\s*\{.*\}\s*\]/s', $text, $matches)) {
                    $jsonText = $matches[0];
                } else {
                    $jsonText = $text;
                }
                $jsonText = str_replace(['```json', '```'], '', trim($jsonText));
                return json_decode($jsonText, true) ?? [];
            }
        } catch (\Exception $e) {
            Log::error("Gemini API Exception (generateMyths): " . $e->getMessage());
        }

        return [];
    }
}
