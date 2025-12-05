<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'story_id',
        'title',
        'description',
        'estimated_hours',
        'logged_hours',
        'status',
        'order',
        'assignee_id',
    ];

    protected $casts = [
        'estimated_hours' => 'decimal:2',
        'logged_hours' => 'decimal:2',
    ];

    protected $appends = [
        'remaining_hours',
    ];

    /**
     * Boot the model
     */
    protected static function boot(): void
    {
        parent::boot();

        static::updated(function ($task) {
            // Update story status when task status changes
            if ($task->isDirty('status')) {
                $task->story->updateStatusFromTasks();
            }
        });
    }

    /**
     * The story this task belongs to
     */
    public function story(): BelongsTo
    {
        return $this->belongsTo(Story::class);
    }

    /**
     * The user assigned to this task
     */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    /**
     * Time logs for this task
     */
    public function timeLogs(): HasMany
    {
        return $this->hasMany(TimeLog::class);
    }

    /**
     * Comments on this task
     */
    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    /**
     * Remaining hours
     */
    public function getRemainingHoursAttribute(): float
    {
        return max(0, $this->estimated_hours - $this->logged_hours);
    }

    /**
     * Log time to this task
     */
    public function logTime(User $user, float $hours, string $description = null, $date = null): TimeLog
    {
        $timeLog = $this->timeLogs()->create([
            'user_id' => $user->id,
            'hours' => $hours,
            'description' => $description,
            'logged_at' => $date ?? now(),
        ]);

        // Update logged hours
        $this->update([
            'logged_hours' => $this->timeLogs()->sum('hours'),
        ]);

        return $timeLog;
    }
}
