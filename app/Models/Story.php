<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Story extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'project_id',
        'epic_id',
        'sprint_id',
        'title',
        'description',
        'acceptance_criteria',
        'estimated_hours',
        'priority',
        'status',
        'order',
        'story_key',
        'assignee_id',
        'reporter_id',
    ];

    protected $casts = [
        'estimated_hours' => 'decimal:2',
    ];

    protected $appends = [
        'logged_hours',
        'remaining_hours',
        'tasks_count',
        'completed_tasks_count',
    ];

    /**
     * Boot the model
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($story) {
            if (!$story->story_key) {
                $project = $story->project;
                $number = $project->getNextStoryNumber();
                $story->story_key = $project->key . '-' . $number;
            }
        });

        static::updated(function ($story) {
            // Update epic status when story status changes
            if ($story->isDirty('status') && $story->epic) {
                $story->epic->updateStatusFromStories();
            }
        });
    }

    /**
     * The project this story belongs to
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * The epic this story belongs to
     */
    public function epic(): BelongsTo
    {
        return $this->belongsTo(Epic::class);
    }

    /**
     * The sprint this story is assigned to
     */
    public function sprint(): BelongsTo
    {
        return $this->belongsTo(Sprint::class);
    }

    /**
     * The user assigned to this story
     */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    /**
     * The user who reported this story
     */
    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    /**
     * Tasks for this story
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class)->orderBy('order');
    }

    /**
     * Comments on this story
     */
    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    /**
     * Total logged hours from tasks
     */
    public function getLoggedHoursAttribute(): float
    {
        return $this->tasks()->sum('logged_hours');
    }

    /**
     * Remaining hours
     */
    public function getRemainingHoursAttribute(): float
    {
        return max(0, $this->estimated_hours - $this->logged_hours);
    }

    /**
     * Tasks count
     */
    public function getTasksCountAttribute(): int
    {
        return $this->tasks()->count();
    }

    /**
     * Completed tasks count
     */
    public function getCompletedTasksCountAttribute(): int
    {
        return $this->tasks()->where('status', 'done')->count();
    }

    /**
     * Scope for backlog stories (not in a sprint)
     */
    public function scopeBacklog($query)
    {
        return $query->whereNull('sprint_id');
    }

    /**
     * Scope for stories by status
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Update status based on tasks
     */
    public function updateStatusFromTasks(): void
    {
        $tasks = $this->tasks;

        if ($tasks->isEmpty()) {
            return;
        }

        $allDone = $tasks->every(fn ($t) => $t->status === 'done');
        $anyInProgress = $tasks->contains(fn ($t) => $t->status === 'in_progress');

        if ($allDone && $this->status !== 'done') {
            $this->update(['status' => 'review']);
        } elseif ($anyInProgress && $this->status === 'todo') {
            $this->update(['status' => 'in_progress']);
        }
    }
}
