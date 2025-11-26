<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Bookmark;
use App\Models\Lecture;

class BookmarkController extends Controller
{
    /**
     * Get all bookmarks for a lecture
     */
    public function index(Request $request, $lectureId)
    {
        $bookmarks = Bookmark::whereHas('lecture', function ($query) use ($request) {
            $query->where('user_id', $request->user()->id);
        })
            ->where('lecture_id', $lectureId)
            ->orderBy('timestamp', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $bookmarks
        ], 200);
    }

    /**
     * Create new bookmark
     */
    public function store(Request $request, $lectureId)
    {
        // Verify lecture ownership
        $lecture = Lecture::where('lecture_id', $lectureId)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $validator = Validator::make($request->all(), [
            'timestamp' => 'required|numeric|min:0',
            'title' => 'nullable|string|max:255',
            'note' => 'nullable|string',
            'priority' => 'required|in:high,medium,low',
            'color' => 'nullable|string|max:7',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $bookmark = Bookmark::create([
            'lecture_id' => $lectureId,
            'timestamp' => $request->timestamp,
            'title' => $request->title,
            'note' => $request->note,
            'priority' => $request->priority,
            'color' => $request->color ?? $this->getColorByPriority($request->priority),
            'is_resolved' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Bookmark created successfully',
            'data' => $bookmark
        ], 201);
    }

    /**
     * Update bookmark
     */
    public function update(Request $request, $lectureId, $id)
    {
        $bookmark = Bookmark::where('bookmark_id', $id)
            ->whereHas('lecture', function ($query) use ($request) {
                $query->where('user_id', $request->user()->id);
            })
            ->firstOrFail();

        $validator = Validator::make($request->all(), [
            'title' => 'nullable|string|max:255',
            'note' => 'nullable|string',
            'priority' => 'sometimes|in:high,medium,low',
            'is_resolved' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $bookmark->update($request->only(['title', 'note', 'priority', 'is_resolved']));

        return response()->json([
            'success' => true,
            'message' => 'Bookmark updated successfully',
            'data' => $bookmark
        ], 200);
    }

    /**
     * Delete bookmark
     */
    public function destroy(Request $request, $lectureId, $id)
    {
        $bookmark = Bookmark::where('bookmark_id', $id)
            ->whereHas('lecture', function ($query) use ($request) {
                $query->where('user_id', $request->user()->id);
            })
            ->firstOrFail();

        $bookmark->delete();

        return response()->json([
            'success' => true,
            'message' => 'Bookmark deleted successfully'
        ], 200);
    }

    /**
     * Helper: Get default color by priority
     */
    private function getColorByPriority($priority)
    {
        return match ($priority) {
            'high' => '#FF6B6B',
            'medium' => '#FFD700',
            'low' => '#87CEEB',
            default => '#D3D3D3',
        };
    }
}
