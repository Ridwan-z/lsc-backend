<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasUuids;

    protected $fillable = [
        'id',
        'name',
        'email',
        'password',
        'avatar_url',
        'major',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    protected $keyType = 'string';
    public $incrementing = false;

    // Relationships
    public function categories()
    {
        return $this->hasMany(Category::class);
    }

    public function lectures()
    {
        return $this->hasMany(Lecture::class);
    }

    public function tags()
    {
        return $this->hasMany(Tag::class);
    }

    public function studySessions()
    {
        return $this->hasMany(StudySession::class);
    }
}
