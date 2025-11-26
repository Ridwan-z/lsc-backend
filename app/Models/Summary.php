<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Summary extends Model
{
    use HasFactory;

    protected $primaryKey = 'summary_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'summary_id',
        'lecture_id',
        'summary_type',
        'content',
        'key_points',
        'keywords',
        'action_items',
        'ai_model',
    ];

    protected $casts = [
        'key_points' => 'array',
        'keywords' => 'array',
        'action_items' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->summary_id)) {
                $model->summary_id = (string) Str::uuid();
            }
        });
    }

    // Relationships
    public function lecture()
    {
        return $this->belongsTo(Lecture::class, 'lecture_id', 'lecture_id');
    }
}
