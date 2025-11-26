<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\LectureController;
use App\Http\Controllers\BookmarkController;
use App\Http\Controllers\TranscriptController;
use App\Http\Controllers\SummaryController;
use App\Http\Controllers\FlashcardController;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\UserController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/test', function () {
    return "API WORKING";
});

// Protected routes
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Categories
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::put('/categories/{id}', [CategoryController::class, 'update']);
    Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);

    // Lectures
    Route::get('/lectures', [LectureController::class, 'index']);
    Route::post('/lectures', [LectureController::class, 'create']);
    Route::get('/lectures/{id}', [LectureController::class, 'show']);
    Route::put('/lectures/{id}', [LectureController::class, 'update']);
    Route::delete('/lectures/{id}', [LectureController::class, 'destroy']);
    Route::post('/lectures/{id}/toggle-favorite', [LectureController::class, 'toggleFavorite']);
    Route::post('/lectures/{id}/play', [LectureController::class, 'play']);
    Route::post('lectures/upload-audio', [LectureController::class, 'uploadAudio']);
    // Bookmarks
    Route::get('/lectures/{lectureId}/bookmarks', [BookmarkController::class, 'index']);
    Route::post('/lectures/{lectureId}/bookmarks', [BookmarkController::class, 'store']);
    Route::put('/lectures/{lectureId}/bookmarks/{id}', [BookmarkController::class, 'update']);
    Route::delete('/lectures/{lectureId}/bookmarks/{id}', [BookmarkController::class, 'destroy']);

    // Transcripts
    Route::get('/lectures/{lectureId}/transcript', [TranscriptController::class, 'show']);
    Route::get('/lectures/{lectureId}/transcript/search', [TranscriptController::class, 'search']);

    // Summaries
    Route::get('/lectures/{lectureId}/summaries', [SummaryController::class, 'index']);
    Route::get('/lectures/{lectureId}/summaries/{type}', [SummaryController::class, 'show']);

    // Flashcards
    Route::get('/lectures/{lectureId}/flashcards', [FlashcardController::class, 'index']);
    Route::post('/flashcards/{id}/review', [FlashcardController::class, 'recordReview']);

    // Quizzes
    Route::get('/lectures/{lectureId}/quizzes', [QuizController::class, 'index']);
    Route::post('/quizzes/{id}/attempt', [QuizController::class, 'submitAnswer']);
    Route::get('/quizzes/my-attempts', [QuizController::class, 'myAttempts']);

    // Statistics
    Route::get('/statistics', [UserController::class, 'statistics']);
    Route::get('/statistics/study-time', [UserController::class, 'studyTime']);
});
