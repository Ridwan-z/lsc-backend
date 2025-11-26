<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Lecture;
use App\Models\StudySession;
use App\Models\QuizAttempt;
use App\Models\Flashcard;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * Get user statistics
     */
    public function statistics(Request $request)
    {
        $user = $request->user();

        // Basic counts
        $totalLectures = Lecture::where('user_id', $user->id)->count();
        $totalDuration = Lecture::where('user_id', $user->id)->sum('duration');
        $totalBookmarks = DB::table('bookmarks')
            ->join('lectures', 'bookmarks.lecture_id', '=', 'lectures.lecture_id')
            ->where('lectures.user_id', $user->id)
            ->count();
        $totalFlashcards = Flashcard::whereHas('lecture', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->count();

        // Recent lectures
        $recentLectures = Lecture::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get(['lecture_id', 'title', 'duration', 'created_at']);

        // Quiz statistics
        $quizStats = QuizAttempt::where('user_id', $user->id)
            ->select(
                DB::raw('COUNT(*) as total_attempts'),
                DB::raw('SUM(CASE WHEN is_correct = true THEN 1 ELSE 0 END) as correct_attempts')
            )
            ->first();

        $quizAccuracy = $quizStats->total_attempts > 0
            ? round(($quizStats->correct_attempts / $quizStats->total_attempts) * 100, 2)
            : 0;

        return response()->json([
            'success' => true,
            'data' => [
                'overview' => [
                    'total_lectures' => $totalLectures,
                    'total_duration' => $totalDuration,
                    'total_bookmarks' => $totalBookmarks,
                    'total_flashcards' => $totalFlashcards,
                ],
                'quizzes' => [
                    'total_attempts' => $quizStats->total_attempts,
                    'correct_attempts' => $quizStats->correct_attempts,
                    'accuracy_rate' => $quizAccuracy,
                ],
                'recent_lectures' => $recentLectures,
            ]
        ], 200);
    }

    /**
     * Get study time statistics
     */
    public function studyTime(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'period' => 'nullable|in:today,week,month,year',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        $period = $request->period ?? 'week';

        // Set date range based on period
        $dateRange = $this->getDateRange($period, $request->start_date, $request->end_date);

        // Study sessions data
        $studySessions = StudySession::where('user_id', $user->id)
            ->whereBetween('started_at', [$dateRange['start'], $dateRange['end']])
            ->select(
                DB::raw('DATE(started_at) as date'),
                DB::raw('SUM(duration) as total_duration'),
                DB::raw('COUNT(*) as session_count')
            )
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        // Total statistics
        $totalStudyTime = $studySessions->sum('total_duration');
        $totalSessions = $studySessions->sum('session_count');

        // Daily average
        $daysCount = max(1, $dateRange['start']->diffInDays($dateRange['end']));
        $averageDailyStudyTime = round($totalStudyTime / $daysCount);

        return response()->json([
            'success' => true,
            'data' => [
                'period' => $period,
                'date_range' => [
                    'start' => $dateRange['start']->toDateString(),
                    'end' => $dateRange['end']->toDateString(),
                ],
                'totals' => [
                    'study_time' => $totalStudyTime,
                    'sessions' => $totalSessions,
                ],
                'averages' => [
                    'daily_study_time' => $averageDailyStudyTime,
                ],
                'daily_breakdown' => $studySessions,
            ]
        ], 200);
    }

    /**
     * Helper method to get date range based on period
     */
    private function getDateRange($period, $startDate = null, $endDate = null)
    {
        if ($startDate && $endDate) {
            return [
                'start' => \Carbon\Carbon::parse($startDate)->startOfDay(),
                'end' => \Carbon\Carbon::parse($endDate)->endOfDay(),
            ];
        }

        $now = now();

        return match ($period) {
            'today' => [
                'start' => $now->copy()->startOfDay(),
                'end' => $now->copy()->endOfDay(),
            ],
            'week' => [
                'start' => $now->copy()->startOfWeek(),
                'end' => $now->copy()->endOfWeek(),
            ],
            'month' => [
                'start' => $now->copy()->startOfMonth(),
                'end' => $now->copy()->endOfMonth(),
            ],
            'year' => [
                'start' => $now->copy()->startOfYear(),
                'end' => $now->copy()->endOfYear(),
            ],
            default => [
                'start' => $now->copy()->startOfWeek(),
                'end' => $now->copy()->endOfWeek(),
            ],
        };
    }
}
