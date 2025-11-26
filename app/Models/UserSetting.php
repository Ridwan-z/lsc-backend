<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class UserSetting extends Model
{
    use HasFactory;

    protected $primaryKey = 'setting_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'setting_id',
        'user_id',
        'default_recording_quality',
        'auto_backup',
        'auto_transcribe',
        'default_playback_speed',
        'skip_silence',
        'notification_enabled',
        'theme',
        'language',
    ];

    protected $casts = [
        'auto_backup' => 'boolean',
        'auto_transcribe' => 'boolean',
        'default_playback_speed' => 'decimal:1',
        'skip_silence' => 'boolean',
        'notification_enabled' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->setting_id)) {
                $model->setting_id = (string) Str::uuid();
            }
        });
    }

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
