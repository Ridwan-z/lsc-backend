<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Lecture extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'lecture_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'lecture_id',
        'user_id',
        'category_id',
        'title',
        'description',
        'audio_url',
        'audio_format',
        'file_size',
        'duration',
        'recording_date',
        'is_favorite',
        'share_token',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'duration' => 'integer',
        'recording_date' => 'date',
        'is_favorite' => 'boolean',
        'deleted_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->lecture_id)) {
                $model->lecture_id = (string) Str::uuid();
            }
            if (empty($model->share_token)) {
                $model->share_token = Str::random(32);
            }
        });
    }

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'category_id');
    }

    public function bookmarks()
    {
        return $this->hasMany(Bookmark::class, 'lecture_id', 'lecture_id');
    }

    public function transcript()
    {
        return $this->hasOne(Transcript::class, 'lecture_id', 'lecture_id');
    }

    public function summaries()
    {
        return $this->hasMany(Summary::class, 'lecture_id', 'lecture_id');
    }

    public function flashcards()
    {
        return $this->hasMany(Flashcard::class, 'lecture_id', 'lecture_id');
    }

    public function quizzes()
    {
        return $this->hasMany(Quiz::class, 'lecture_id', 'lecture_id');
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'lecture_tags', 'lecture_id', 'tag_id');
    }

    public function studySessions()
    {
        return $this->hasMany(StudySession::class, 'lecture_id', 'lecture_id');
    }

    // Scopes
    public function scopeFavorites($query)
    {
        return $query->where('is_favorite', true);
    }

    // Helper method
    public function toggleFavorite()
    {
        $this->is_favorite = !$this->is_favorite;
        $this->save();
    }
}
