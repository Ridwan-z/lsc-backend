<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenAIService
{
    protected $apiKey;
    protected $baseUrl = 'https://api.openai.com/v1/chat/completions';
    protected $model = 'gpt-4';

    public function __construct()
    {
        $this->apiKey = config('services.openai.key');
    }

    /**
     * Generate summary from transcript
     * 
     * @param string $transcript Full transcript text
     * @param string $type Summary type (brief/standard/detailed)
     * @return array Summary data
     */
    public function generateSummary($transcript, $type = 'standard')
    {
        $prompt = $this->buildSummaryPrompt($transcript, $type);

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(60)->post($this->baseUrl, [
                'model' => $this->model,
                'messages' => [
                    ['role' => 'system', 'content' => 'You are an expert at summarizing educational content.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'temperature' => 0.7,
                'max_tokens' => $this->getMaxTokens($type),
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $content = $data['choices'][0]['message']['content'];

                return [
                    'success' => true,
                    'content' => $content,
                    'key_points' => $this->extractKeyPoints($content),
                    'keywords' => $this->extractKeywords($content),
                ];
            }

            return [
                'success' => false,
                'error' => $response->json()['error']['message'] ?? 'Summary generation failed',
            ];
        } catch (\Exception $e) {
            Log::error('OpenAI API Error: ' . $e->getMessage());

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Generate flashcards from transcript
     */
    public function generateFlashcards($transcript, $count = 10)
    {
        $prompt = "Based on this lecture transcript, generate {$count} educational flashcards in JSON format.\n\n";
        $prompt .= "Format: [{\"question\": \"...\", \"answer\": \"...\", \"difficulty\": \"easy|medium|hard\"}]\n\n";
        $prompt .= "Transcript:\n{$transcript}";

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(60)->post($this->baseUrl, [
                'model' => $this->model,
                'messages' => [
                    ['role' => 'system', 'content' => 'You are an expert at creating educational flashcards.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'temperature' => 0.8,
                'max_tokens' => 2000,
            ]);

            if ($response->successful()) {
                $content = $response->json()['choices'][0]['message']['content'];

                // Try to parse JSON from response
                $flashcards = json_decode($content, true);

                if (json_last_error() === JSON_ERROR_NONE) {
                    return [
                        'success' => true,
                        'flashcards' => $flashcards,
                    ];
                }
            }

            return [
                'success' => false,
                'error' => 'Failed to generate flashcards',
            ];
        } catch (\Exception $e) {
            Log::error('OpenAI Flashcard Generation Error: ' . $e->getMessage());

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Generate quiz questions from transcript
     */
    public function generateQuiz($transcript, $count = 5)
    {
        $prompt = "Based on this lecture transcript, generate {$count} multiple-choice quiz questions in JSON format.\n\n";
        $prompt .= "Format: [{\"question\": \"...\", \"options\": [\"A\", \"B\", \"C\", \"D\"], \"correct_answer\": \"A\", \"explanation\": \"...\"}]\n\n";
        $prompt .= "Transcript:\n{$transcript}";

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(60)->post($this->baseUrl, [
                'model' => $this->model,
                'messages' => [
                    ['role' => 'system', 'content' => 'You are an expert at creating educational quizzes.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'temperature' => 0.8,
                'max_tokens' => 2000,
            ]);

            if ($response->successful()) {
                $content = $response->json()['choices'][0]['message']['content'];

                // Try to parse JSON from response
                $quizzes = json_decode($content, true);

                if (json_last_error() === JSON_ERROR_NONE) {
                    return [
                        'success' => true,
                        'quizzes' => $quizzes,
                    ];
                }
            }

            return [
                'success' => false,
                'error' => 'Failed to generate quiz',
            ];
        } catch (\Exception $e) {
            Log::error('OpenAI Quiz Generation Error: ' . $e->getMessage());

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Build prompt for summary generation
     */
    private function buildSummaryPrompt($transcript, $type)
    {
        $prompts = [
            'brief' => "Summarize this lecture in 5 key bullet points. Be concise and focus on the most important concepts.\n\nTranscript:\n{$transcript}",
            'standard' => "Create a comprehensive summary of this lecture in 1-2 paragraphs. Include main topics, key concepts, and important takeaways.\n\nTranscript:\n{$transcript}",
            'detailed' => "Create a detailed outline of this lecture with all main topics and subtopics. Include key concepts, examples, and important details.\n\nTranscript:\n{$transcript}",
        ];

        return $prompts[$type] ?? $prompts['standard'];
    }

    /**
     * Get max tokens based on summary type
     */
    private function getMaxTokens($type)
    {
        return match ($type) {
            'brief' => 500,
            'standard' => 1000,
            'detailed' => 2000,
            default => 1000,
        };
    }

    /**
     * Extract key points from summary content
     */
    private function extractKeyPoints($content)
    {
        // Simple extraction: look for bullet points or numbered lists
        preg_match_all('/^[\-\*\d+\.]\s+(.+)$/m', $content, $matches);

        return array_slice($matches[1] ?? [], 0, 10);
    }

    /**
     * Extract keywords from summary content
     */
    private function extractKeywords($content)
    {
        // Simple keyword extraction (you can use more sophisticated NLP here)
        $words = str_word_count(strtolower($content), 1);
        $stopWords = ['the', 'is', 'at', 'which', 'on', 'and', 'a', 'an', 'as', 'are', 'was', 'were'];
        $keywords = array_diff($words, $stopWords);

        // Get word frequency
        $frequency = array_count_values($keywords);
        arsort($frequency);

        // Return top 10 keywords
        return array_keys(array_slice($frequency, 0, 10));
    }
}
