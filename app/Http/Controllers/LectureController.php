<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Models\Lecture;

class LectureController extends Controller
{
    /**
     * Upload audio file from Arduino ESP32
     */
    public function uploadAudio(Request $request)
    {
        // Get lecture_id from header
        $lectureId = $request->header('X-Lecture-ID');
        $fileSize = $request->header('X-File-Size');
        $duration = $request->header('X-Duration');

        if (!$lectureId) {
            return response()->json([
                'success' => false,
                'message' => 'Lecture ID is required'
            ], 400);
        }

        // Find lecture
        $lecture = Lecture::where('lecture_id', $lectureId)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$lecture) {
            return response()->json([
                'success' => false,
                'message' => 'Lecture not found'
            ], 404);
        }

        // Get raw audio data from request body
        $audioData = $request->getContent();

        if (empty($audioData)) {
            return response()->json([
                'success' => false,
                'message' => 'No audio data received'
            ], 400);
        }

        // Generate filename
        $fileName = 'lecture_' . $lectureId . '_' . time() . '.wav';
        $path = 'lectures/' . $request->user()->id . '/' . $fileName;

        // Save to storage
        Storage::disk('public')->put($path, $audioData);

        // Update lecture
        $lecture->update([
            'audio_url' => Storage::url($path),
            'audio_format' => 'wav',
            'file_size' => $fileSize ?? strlen($audioData),
            'duration' => $duration ?? 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Audio uploaded successfully',
            'data' => [
                'lecture_id' => $lecture->lecture_id,
                'audio_url' => $lecture->audio_url,
                'file_size' => $lecture->file_size,
                'duration' => $lecture->duration,
            ]
        ], 200);
    }

    /**
     * Get all lectures for authenticated user
     */
    public function index(Request $request)
    {
        $lectures = Lecture::where('user_id', $request->user()->id)
            ->with(['category', 'bookmarks', 'tags'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $lectures
        ], 200);
    }

    /**
     * Create lecture (audio file optional)
     */
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:categories,category_id',
            'recording_date' => 'required|date',
            'audio_file' => 'nullable|file|mimes:mp3,m4a,wav|max:512000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $audioUrl = '';
        $audioFormat = 'mp3';
        $fileSize = 0;
        $duration = 0;

        if ($request->hasFile('audio_file')) {
            $audioPath = $request->file('audio_file')->store('lectures/' . $request->user()->id, 'public');
            $audioUrl = Storage::url($audioPath);
            $fileSize = $request->file('audio_file')->getSize();
            $audioFormat = $request->file('audio_file')->extension();
        }

        $lecture = Lecture::create([
            'user_id' => $request->user()->id,
            'category_id' => $request->category_id,
            'title' => $request->title,
            'description' => $request->description,
            'audio_url' => $audioUrl,
            'audio_format' => $audioFormat,
            'file_size' => $fileSize,
            'duration' => $duration,
            'recording_date' => $request->recording_date,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Lecture created successfully',
            'data' => $lecture->fresh(['category', 'bookmarks', 'tags'])
        ], 201);
    }

    /**
     * Get specific lecture
     */
    public function show(Request $request, $id)
    {
        $lecture = Lecture::where('lecture_id', $id)
            ->where('user_id', $request->user()->id)
            ->with(['category', 'bookmarks', 'transcript', 'summaries', 'flashcards', 'quizzes', 'tags'])
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $lecture
        ], 200);
    }

    /**
     * Update lecture
     */
    public function update(Request $request, $id)
    {
        $lecture = Lecture::where('lecture_id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:categories,category_id',
            'is_favorite' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $lecture->update($request->only([
            'title',
            'description',
            'category_id',
            'is_favorite'
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Lecture updated successfully',
            'data' => $lecture
        ], 200);
    }

    /**
     * Delete lecture (soft delete)
     */
    public function destroy(Request $request, $id)
    {
        $lecture = Lecture::where('lecture_id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $lecture->delete();

        return response()->json([
            'success' => true,
            'message' => 'Lecture moved to trash'
        ], 200);
    }

    /**
     * Toggle favorite
     */
    public function toggleFavorite(Request $request, $id)
    {
        $lecture = Lecture::where('lecture_id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $lecture->toggleFavorite();

        return response()->json([
            'success' => true,
            'message' => 'Favorite status updated',
            'data' => ['is_favorite' => $lecture->is_favorite]
        ], 200);
    }
}
