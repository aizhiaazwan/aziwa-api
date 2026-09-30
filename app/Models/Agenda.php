<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Agenda extends Model
{
    protected $table = 'agendas';

    protected $fillable = [
        'title', 'description', 'date', 'start_time', 'end_time', 'category', 'reminder',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date:Y-m-d',
            'reminder' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reminders(): HasMany
    {
        return $this->hasMany(Reminder::class);
    }
}