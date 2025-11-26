<?php

namespace App\Jobs;

use App\Models\Lecture;
use App\Models\Transcript;
use App\Models\TranscriptSegment;
use App\Models\Summary;
use App\Models\Flashcard;
use App\Models\Quiz;
use App\Services\WhisperService;
use App\Services\OpenAIService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ProcessAudioJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $lecture;

    /**
     * Create a new job instance.
     */
    public function __construct(Lecture $lecture)
    {
        $this->lecture = $lecture;
    }

    /**
     * Execute the job.
     */
    public function handle(WhisperService $whisper, OpenAIService $openai)
    {
        try {
            // Update status
            $this->updateProgress(10, 'Starting transcription...');

            // Step 1: Transcribe audio
            $audioPath = public_path(str_replace('/storage/', 'storage/', parse_url($this->lecture->audio_url, PHP_URL_PATH)));
            $transcriptionResult = $whisper->transcribe($audioPath, 'id');

            if (!$transcriptionResult['success']) {
                $this->fail($transcriptionResult['error']);
                return;
            }

            $this->updateProgress(40, 'Transcription complete');

            // Step 2: Save transcript
            $transcript = Transcript::create([
                'lecture_id' => $this->lecture->lecture_id,
                'full_text' => $transcriptionResult['text'],
                'language' => $transcriptionResult['language'],
                'word_count' => $whisper->calculateWordCount($transcriptionResult['text']),
                'processing_time' => 0, // Calculate actual time
                'stt_provider' => 'whisper',
            ]);

            // Step 3: Save transcript segments
            $segments = $whisper->formatSegments($transcriptionResult['segments'] ?? []);
            foreach ($segments as $segment) {
                TranscriptSegment::create([
                    'transcript_id' => $transcript->transcript_id,
                    'text' => $segment['text'],
                    'start_time' => $segment['start_time'],
                    'end_time' => $segment['end_time'],
                    'confidence' => $segment['confidence'],
                    'sequence_number' => $segment['sequence_number'],
                ]);
            }

            $this->updateProgress(60, 'Generating summaries...');

            // Step 4: Generate summaries
            $summaryTypes = ['brief', 'standard', 'detailed'];
            foreach ($summaryTypes as $type) {
                $summaryResult = $openai->generateSummary($transcriptionResult['text'], $type);

                if ($summaryResult['success']) {
                    Summary::create([
                        'lecture_id' => $this->lecture->lecture_id,
                        'summary_type' => $type,
                        'content' => $summaryResult['content'],
                        'key_points' => $summaryResult['key_points'],
                        'keywords' => $summaryResult['keywords'],
                        'ai_model' => 'gpt-4',
                    ]);
                }
            }

            $this->updateProgress(80, 'Generating study tools...');

            // Step 5: Generate flashcards
            $flashcardsResult = $openai->generateFlashcards($transcriptionResult['text'], 15);
            if ($flashcardsResult['success']) {
                foreach ($flashcardsResult['flashcards'] as $card) {
                    Flashcard::create([
                        'lecture_id' => $this->lecture->lecture_id,
                        'question' => $card['question'],
                        'answer' => $card['answer'],
                        'difficulty' => $card['difficulty'] ?? 'medium',
                    ]);
                }
            }

            // Step 6: Generate quizzes
            $quizzesResult = $openai->generateQuiz($transcriptionResult['text'], 10);
            if ($quizzesResult['success']) {
                foreach ($quizzesResult['quizzes'] as $quiz) {
                    Quiz::create([
                        'lecture_id' => $this->lecture->lecture_id,
                        'question' => $quiz['question'],
                        'question_type' => 'multiple_choice',
                        'options' => $quiz['options'],
                        'correct_answer' => $quiz['correct_answer'],
                        'explanation' => $quiz['explanation'] ?? null,
                    ]);
                }
            }

            // Complete
            $this->updateProgress(100, 'Processing complete!');
            $this->lecture->update(['status' => 'completed']);

            Log::info('Audio processing completed for lecture: ' . $this->lecture->lecture_id);
        } catch (\Exception $e) {
            Log::error('Audio processing failed: ' . $e->getMessage());
            $this->fail($e->getMessage());
        }
    }

    /**
     * Update processing progress
     */
    private function updateProgress($progress, $message = null)
    {
        $this->lecture->update([
            'processing_progress' => $progress,
        ]);

        if ($message) {
            Log::info("Lecture {$this->lecture->lecture_id}: {$message} ({$progress}%)");
        }
    }

    /**
     * Handle failure
     */
    private function fail($errorMessage)
    {
        $this->lecture->update([
            'status' => 'failed',
            'processing_progress' => 0,
        ]);

        Log::error("Lecture processing failed: {$errorMessage}");
    }
}
