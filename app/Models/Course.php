<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    protected $fillable = [
        'name', 'code', 'sks', 'lecturer', 'day', 'start_time', 'end_time',
        'room', 'semester', 'description', 'icon', 'tone', 'stripe',
    ];

    protected function casts(): array
    {
        return [
            'sks' => 'integer',
            'day' => 'integer',
            'semester' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }
}