<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Tag extends Model
{
    use HasFactory;

    protected $primaryKey = 'tag_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'tag_id',
        'user_id',
        'name',
        'color',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->tag_id)) {
                $model->tag_id = (string) Str::uuid();
            }
        });
    }

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function lectures()
    {
        return $this->belongsToMany(Lecture::class, 'lecture_tags', 'tag_id', 'lecture_id');
    }
}
