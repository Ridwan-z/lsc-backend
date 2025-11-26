<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Lecture;
use App\Models\Transcript;
use App\Models\TranscriptSegment;

class TranscriptController extends Controller
{
    /**
     * Get transcript for a lecture
     */
    public function show(Request $request, $lectureId)
    {
        // Verify lecture ownership
        $lecture = Lecture::where('lecture_id', $lectureId)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $transcript = Transcript::where('lecture_id', $lectureId)
            ->with(['segments' => function ($query) {
                $query->orderBy('sequence_number', 'asc');
            }])
            ->first();

        if (!$transcript) {
            return response()->json([
                'success' => false,
                'message' => 'Transcript not found or still processing'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $transcript
        ], 200);
    }

    /**
     * Search in transcript text
     */
    public function search(Request $request, $lectureId)
    {
        $validator = Validator::make($request->all(), [
            'query' => 'required|string|min:2|max:100',
            'limit' => 'nullable|integer|min:1|max:50',
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

        $query = $request->query;
        $limit = $request->limit ?? 20;

        // Search in transcript segments
        $segments = TranscriptSegment::whereHas('transcript', function ($q) use ($lectureId) {
            $q->where('lecture_id', $lectureId);
        })
            ->where('text', 'like', "%{$query}%")
            ->orderBy('start_time', 'asc')
            ->limit($limit)
            ->get();

        // Format results with context
        $results = $segments->map(function ($segment) {
            return [
                'segment_id' => $segment->segment_id,
                'text' => $segment->text,
                'start_time' => $segment->start_time,
                'end_time' => $segment->end_time,
                'speaker' => $segment->speaker,
                'confidence' => $segment->confidence,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'query' => $query,
                'total_results' => $segments->count(),
                'results' => $results
            ]
        ], 200);
    }
}
