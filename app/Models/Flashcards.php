<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Flashcard extends Model
{
    use HasFactory;

    protected $primaryKey = 'flashcard_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'flashcard_id',
        'lecture_id',
        'question',
        'answer',
        'difficulty',
        'review_count',
        'correct_count',
        'next_review_at',
    ];

    protected $casts = [
        'review_count' => 'integer',
        'correct_count' => 'integer',
        'next_review_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->flashcard_id)) {
                $model->flashcard_id = (string) Str::uuid();
            }
        });
    }

    // Relationships
    public function lecture()
    {
        return $this->belongsTo(Lecture::class, 'lecture_id', 'lecture_id');
    }

    // Helper methods
    public function recordReview($correct)
    {
        $this->review_count++;
        if ($correct) {
            $this->correct_count++;
        }

        // Spaced repetition algorithm
        $interval = $this->calculateNextReview($correct);
        $this->next_review_at = now()->addDays($interval);
        $this->save();
    }

    private function calculateNextReview($correct)
    {
        if ($correct) {
            // Correct answer: increase interval
            return match ($this->difficulty) {
                'easy' => 7,
                'medium' => 3,
                'hard' => 1,
            };
        } else {
            // Incorrect: review tomorrow
            return 1;
        }
    }
}
