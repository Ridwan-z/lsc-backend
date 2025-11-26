<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Lecture;
use App\Models\Quiz;
use App\Models\QuizAttempt;

class QuizController extends Controller
{
    /**
     * Get all quizzes for a lecture
     */
    public function index(Request $request, $lectureId)
    {
        // Verify lecture ownership
        $lecture = Lecture::where('lecture_id', $lectureId)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $quizzes = Quiz::where('lecture_id', $lectureId)
            ->withCount('attempts')
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $quizzes
        ], 200);
    }

    /**
     * Submit quiz answer
     */
    public function submitAnswer(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'user_answer' => 'required|string',
            'time_taken' => 'nullable|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $quiz = Quiz::where('quiz_id', $id)
            ->whereHas('lecture', function ($query) use ($request) {
                $query->where('user_id', $request->user()->id);
            })
            ->firstOrFail();

        // Calculate score based on question type
        $isCorrect = $this->evaluateAnswer($quiz, $request->user_answer);
        $score = $isCorrect ? 100 : 0;

        $attempt = QuizAttempt::create([
            'quiz_id' => $id,
            'user_id' => $request->user()->id,
            'user_answer' => $request->user_answer,
            'is_correct' => $isCorrect,
            'time_taken' => $request->time_taken,
            'score' => $score,
            'attempted_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => $isCorrect ? 'Correct answer!' : 'Incorrect answer',
            'data' => [
                'attempt' => $attempt,
                'correct_answer' => $quiz->correct_answer,
                'explanation' => $quiz->explanation,
                'is_correct' => $isCorrect
            ]
        ], 200);
    }

    /**
     * Get user's quiz attempts
     */
    public function myAttempts(Request $request)
    {
        $attempts = QuizAttempt::where('user_id', $request->user()->id)
            ->with(['quiz.lecture'])
            ->orderBy('attempted_at', 'desc')
            ->paginate(20);

        // Calculate statistics
        $totalAttempts = $attempts->total();
        $correctAttempts = QuizAttempt::where('user_id', $request->user()->id)
            ->where('is_correct', true)
            ->count();
        $accuracy = $totalAttempts > 0 ? round(($correctAttempts / $totalAttempts) * 100, 2) : 0;

        return response()->json([
            'success' => true,
            'data' => [
                'attempts' => $attempts,
                'statistics' => [
                    'total_attempts' => $totalAttempts,
                    'correct_attempts' => $correctAttempts,
                    'accuracy_rate' => $accuracy
                ]
            ]
        ], 200);
    }

    /**
     * Evaluate answer based on question type
     */
    private function evaluateAnswer(Quiz $quiz, $userAnswer)
    {
        $correctAnswer = strtolower(trim($quiz->correct_answer));
        $userAnswer = strtolower(trim($userAnswer));

        switch ($quiz->question_type) {
            case 'multiple_choice':
            case 'true_false':
                return $correctAnswer === $userAnswer;

            case 'short_answer':
                // For short answer, allow some flexibility
                $similarity = similar_text($correctAnswer, $userAnswer, $percent);
                return $percent >= 80; // 80% similarity threshold

            case 'essay':
                // For essays, we might need more complex evaluation
                // For now, just check if answer is not empty
                return !empty(trim($userAnswer));

            default:
                return false;
        }
    }
}
