<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class QuizAttempt extends Model
{
    use HasFactory;

    protected $primaryKey = 'attempt_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'attempt_id',
        'quiz_id',
        'user_id',
        'user_answer',
        'is_correct',
        'time_taken',
        'score',
        'attempted_at',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
        'time_taken' => 'integer',
        'score' => 'integer',
        'attempted_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->attempt_id)) {
                $model->attempt_id = (string) Str::uuid();
            }
        });
    }

    // Relationships
    public function quiz()
    {
        return $this->belongsTo(Quiz::class, 'quiz_id', 'quiz_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
