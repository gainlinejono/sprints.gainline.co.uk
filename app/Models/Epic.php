<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Epic extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'project_id',
        'name',
        'description',
        'color',
        'priority',
        'status',
        'target_date',
    ];

    protected $casts = [
        'target_date' => 'date',
    ];

    protected $appends = [
        'total_hours',
        'completed_hours',
        'progress_percentage',
        'stories_count',
    ];

    /**
     * The project this epic belongs to
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Stories in this epic
     */
    public function stories(): HasMany
    {
        return $this->hasMany(Story::class);
    }

    /**
     * Total estimated hours
     */
    public function getTotalHoursAttribute(): float
    {
        return $this->stories()->sum('estimated_hours');
    }

    /**
     * Completed hours
     */
    public function getCompletedHoursAttribute(): float
    {
        return $this->stories()->where('status', 'done')->sum('estimated_hours');
    }

    /**
     * Progress percentage
     */
    public function getProgressPercentageAttribute(): float
    {
        if ($this->total_hours === 0) {
            return 0;
        }
        return round(($this->completed_hours / $this->total_hours) * 100, 1);
    }

    /**
     * Stories count
     */
    public function getStoriesCountAttribute(): int
    {
        return $this->stories()->count();
    }

    /**
     * Update status based on stories
     */
    public function updateStatusFromStories(): void
    {
        $stories = $this->stories;

        if ($stories->isEmpty()) {
            $this->update(['status' => 'open']);
            return;
        }

        $allDone = $stories->every(fn ($s) => $s->status === 'done');
        $anyInProgress = $stories->contains(fn ($s) => in_array($s->status, ['in_progress', 'review']));

        if ($allDone) {
            $this->update(['status' => 'completed']);
        } elseif ($anyInProgress) {
            $this->update(['status' => 'in_progress']);
        } else {
            $this->update(['status' => 'open']);
        }
    }
}
