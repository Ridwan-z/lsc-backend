<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Transcript extends Model
{
    use HasFactory;

    protected $primaryKey = 'transcript_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'transcript_id',
        'lecture_id',
        'full_text',
        'language',
        'confidence_score',
        'word_count',
        'processing_time',
        'stt_provider',
    ];

    protected $casts = [
        'confidence_score' => 'decimal:2',
        'word_count' => 'integer',
        'processing_time' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->transcript_id)) {
                $model->transcript_id = (string) Str::uuid();
            }
        });
    }

    // Relationships
    public function lecture()
    {
        return $this->belongsTo(Lecture::class, 'lecture_id', 'lecture_id');
    }

    public function segments()
    {
        return $this->hasMany(TranscriptSegment::class, 'transcript_id', 'transcript_id');
    }
}
