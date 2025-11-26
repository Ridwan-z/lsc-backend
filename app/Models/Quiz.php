<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Quiz extends Model
{
    use HasFactory;

    protected $primaryKey = 'quiz_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'quiz_id',
        'lecture_id',
        'question',
        'question_type',
        'options',
        'correct_answer',
        'explanation',
    ];

    protected $casts = [
        'options' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->quiz_id)) {
                $model->quiz_id = (string) Str::uuid();
            }
        });
    }

    // Relationships
    public function lecture()
    {
        return $this->belongsTo(Lecture::class, 'lecture_id', 'lecture_id');
    }

    public function attempts()
    {
        return $this->hasMany(QuizAttempt::class, 'quiz_id', 'quiz_id');
    }
}
