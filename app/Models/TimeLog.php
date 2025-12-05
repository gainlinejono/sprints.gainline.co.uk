<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimeLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id',
        'user_id',
        'hours',
        'description',
        'logged_at',
    ];

    protected $casts = [
        'hours' => 'decimal:2',
        'logged_at' => 'date',
    ];

    /**
     * The task this time log belongs to
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    /**
     * The user who logged this time
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
