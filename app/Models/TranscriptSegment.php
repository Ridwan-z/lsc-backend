<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class TranscriptSegment extends Model
{
    use HasFactory;

    protected $primaryKey = 'segment_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'segment_id',
        'transcript_id',
        'text',
        'start_time',
        'end_time',
        'speaker',
        'confidence',
        'sequence_number',
    ];

    protected $casts = [
        'start_time' => 'decimal:2',
        'end_time' => 'decimal:2',
        'confidence' => 'decimal:2',
        'sequence_number' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->segment_id)) {
                $model->segment_id = (string) Str::uuid();
            }
        });
    }

    // Relationships
    public function transcript()
    {
        return $this->belongsTo(Transcript::class, 'transcript_id', 'transcript_id');
    }
}
