<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Lecture;
use App\Models\Flashcard;

class FlashcardController extends Controller
{
    /**
     * Get all flashcards for a lecture
     */
    public function index(Request $request, $lectureId)
    {
        // Verify lecture ownership
        $lecture = Lecture::where('lecture_id', $lectureId)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $flashcards = Flashcard::where('lecture_id', $lectureId)
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $flashcards
        ], 200);
    }

    /**
     * Record flashcard review result
     */
    public function recordReview(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'correct' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $flashcard = Flashcard::where('flashcard_id', $id)
            ->whereHas('lecture', function ($query) use ($request) {
                $query->where('user_id', $request->user()->id);
            })
            ->firstOrFail();

        $flashcard->recordReview($request->correct);

        return response()->json([
            'success' => true,
            'message' => 'Review recorded successfully',
            'data' => [
                'flashcard' => $flashcard,
                'next_review_at' => $flashcard->next_review_at
            ]
        ], 200);
    }

    /**
     * Get due flashcards for review (optional additional method)
     */
    public function dueFlashcards(Request $request)
    {
        $dueFlashcards = Flashcard::whereHas('lecture', function ($query) use ($request) {
            $query->where('user_id', $request->user()->id);
        })
            ->where(function ($query) {
                $query->where('next_review_at', '<=', now())
                    ->orWhereNull('next_review_at');
            })
            ->orderBy('next_review_at', 'asc')
            ->limit(50)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'total_due' => $dueFlashcards->count(),
                'flashcards' => $dueFlashcards
            ]
        ], 200);
    }
}
