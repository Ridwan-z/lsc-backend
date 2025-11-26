<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Bookmark extends Model
{
    use HasFactory;

    protected $primaryKey = 'bookmark_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'bookmark_id',
        'lecture_id',
        'timestamp',
        'title',
        'note',
        'priority',
        'color',
        'is_resolved',
    ];

    protected $casts = [
        'timestamp' => 'decimal:2',
        'is_resolved' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->bookmark_id)) {
                $model->bookmark_id = (string) Str::uuid();
            }
        });
    }

    // Relationships
    public function lecture()
    {
        return $this->belongsTo(Lecture::class, 'lecture_id', 'lecture_id');
    }

    // Scopes
    public function scopeHighPriority($query)
    {
        return $query->where('priority', 'high');
    }

    public function scopeUnresolved($query)
    {
        return $query->where('is_resolved', false);
    }
}
