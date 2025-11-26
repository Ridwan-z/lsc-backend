<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhisperService
{
    protected $apiKey;
    protected $baseUrl = 'https://api.openai.com/v1/audio';

    public function __construct()
    {
        $this->apiKey = config('services.openai.key');
    }

    /**
     * Transcribe audio file using Whisper API
     * 
     * @param string $audioPath Path to audio file
     * @param string $language Language code (id/en)
     * @return array Transcript data
     */
    public function transcribe($audioPath, $language = 'id')
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
            ])->attach(
                'file',
                file_get_contents($audioPath),
                basename($audioPath)
            )->post($this->baseUrl . '/transcriptions', [
                'model' => 'whisper-1',
                'language' => $language,
                'response_format' => 'verbose_json',
                'timestamp_granularities' => ['segment'],
            ]);

            if ($response->successful()) {
                $data = $response->json();

                return [
                    'success' => true,
                    'text' => $data['text'],
                    'language' => $data['language'] ?? $language,
                    'duration' => $data['duration'] ?? 0,
                    'segments' => $data['segments'] ?? [],
                ];
            }

            return [
                'success' => false,
                'error' => $response->json()['error']['message'] ?? 'Transcription failed',
            ];
        } catch (\Exception $e) {
            Log::error('Whisper API Error: ' . $e->getMessage());

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Calculate word count from text
     */
    public function calculateWordCount($text)
    {
        return str_word_count($text);
    }

    /**
     * Format segments for database storage
     */
    public function formatSegments($segments)
    {
        $formatted = [];

        foreach ($segments as $index => $segment) {
            $formatted[] = [
                'text' => $segment['text'] ?? '',
                'start_time' => $segment['start'] ?? 0,
                'end_time' => $segment['end'] ?? 0,
                'confidence' => $segment['confidence'] ?? null,
                'sequence_number' => $index + 1,
            ];
        }

        return $formatted;
    }
}
