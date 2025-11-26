<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class StudySession extends Model
{
    use HasFactory;

    protected $primaryKey = 'session_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'session_id',
        'user_id',
        'lecture_id',
        'duration',
        'session_type',
        'completed',
        'started_at',
        'ended_at',
    ];

    protected $casts = [
        'duration' => 'integer',
        'completed' => 'boolean',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->session_id)) {
                $model->session_id = (string) Str::uuid();
            }
        });
    }

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function lecture()
    {
        return $this->belongsTo(Lecture::class, 'lecture_id', 'lecture_id');
    }

    // Helper methods
    public function endSession()
    {
        $this->ended_at = now();
        $this->duration = $this->started_at->diffInSeconds($this->ended_at);
        $this->completed = true;
        $this->save();
    }
}
