<?php

namespace App\Http\Controllers;

use App\Models\ChatBot;
use App\Models\KnowledgeBase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;


class ChatbotController extends Controller
{
    private $openaiApiKey;
    private $openaiEndpoint = 'https://api.openai.com/v1/chat/completions';

    public function __construct()
    {
        $this->openaiApiKey = env('OPENAI_API_KEY');
    }

    public function ask(Request $request)
    {
        $validator = $request->validate([
            'message' => 'required|string',
            'history' => 'sometimes|array'
        ]);

        try {
            // Create system prompt with poultry farming context
            $systemPrompt = "You are PoultryBot, an AI assistant specialized in poultry farming in Ghana.
                Provide accurate, practical advice about chicken, turkey, and other poultry farming including:
                - Breeding and hatchery management
                - Feeding and nutrition
                - Disease prevention and treatment
                - Housing and equipment
                - Business and marketing
                - Common challenges in Ghana's climate
                Keep answers concise (2-3 paragraphs max) and factual.
                For medical advice, always recommend consulting a veterinarian.
                If asked about products or farms, suggest checking the platform's marketplace.";

            // Prepare messages array
            $messages = [
                ['role' => 'system', 'content' => $systemPrompt]
            ];

            // Add conversation history if provided
            if (!empty($request->history)) {
                foreach ($request->history as $item) {
                    $messages[] = ['role' => 'user', 'content' => $item['question']];
                    $messages[] = ['role' => 'assistant', 'content' => $item['answer']];
                }
            }

            // Add current message
            $messages[] = ['role' => 'user', 'content' => $request->message];

            // Call OpenAI API
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->openaiApiKey,
                'Content-Type' => 'application/json',
            ])->post($this->openaiEndpoint, [
                'model' => 'gpt-3.5-turbo',
                'messages' => $messages,
                'temperature' => 0.7,
                'max_tokens' => 500,
            ]);

            $responseData = $response->json();

            if (isset($responseData['choices'][0]['message']['content'])) {
                $answer = $responseData['choices'][0]['message']['content'];

                // Log the conversation
                ChatBot::create([
                    'user_id' => auth()->id(),
                    'question' => $request->message,
                    'answer' => $answer,
                    'metadata' => json_encode($responseData)
                ]);

                return response()->json([
                    'answer' => $answer,
                    'sources' => $this->getRelatedArticles($request->message)
                ]);
            }

            return response()->json([
                'error' => 'Unable to process your request'
            ], 500);

        } catch (\Exception $e) {
            Log::error('Chatbot error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Our poultry expert is currently unavailable. Please try again later.'
            ], 503);
        }
    }

    private function getRelatedArticles($question)
    {
        return KnowledgeBase::where('is_published', true)
            ->where(function($query) use ($question) {
                $query->where('title', 'like', '%'.$question.'%')
                    ->orWhere('content', 'like', '%'.$question.'%');
            })
            ->limit(3)
            ->get(['id', 'title', 'summary']);
    }
}
