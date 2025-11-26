<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Lecture;
use App\Models\Summary;

class SummaryController extends Controller
{
    /**
     * Get all summaries for a lecture
     */
    public function index(Request $request, $lectureId)
    {
        // Verify lecture ownership
        $lecture = Lecture::where('lecture_id', $lectureId)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $summaries = Summary::where('lecture_id', $lectureId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $summaries
        ], 200);
    }

    /**
     * Get specific summary by type
     */
    public function show(Request $request, $lectureId, $type)
    {
        $validator = Validator::make(['type' => $type], [
            'type' => 'required|in:brief,standard,detailed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Verify lecture ownership
        $lecture = Lecture::where('lecture_id', $lectureId)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $summary = Summary::where('lecture_id', $lectureId)
            ->where('summary_type', $type)
            ->first();

        if (!$summary) {
            return response()->json([
                'success' => false,
                'message' => "{$type} summary not found"
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $summary
        ], 200);
    }
}
