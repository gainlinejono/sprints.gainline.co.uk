<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'key',
        'color',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected $appends = [
        'total_hours',
        'completed_hours',
    ];

    /**
     * Project members
     */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_members')
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * Project sprints
     */
    public function sprints(): HasMany
    {
        return $this->hasMany(Sprint::class);
    }

    /**
     * Project epics
     */
    public function epics(): HasMany
    {
        return $this->hasMany(Epic::class);
    }

    /**
     * Project stories
     */
    public function stories(): HasMany
    {
        return $this->hasMany(Story::class);
    }

    /**
     * Get the active sprint
     */
    public function activeSprint()
    {
        return $this->sprints()->where('status', 'active')->first();
    }

    /**
     * Get next story number
     */
    public function getNextStoryNumber(): int
    {
        return $this->stories()->withTrashed()->count() + 1;
    }

    /**
     * Get next sprint number
     */
    public function getNextSprintNumber(): int
    {
        return $this->sprints()->withTrashed()->count() + 1;
    }

    /**
     * Total estimated hours for all stories
     */
    public function getTotalHoursAttribute(): float
    {
        return $this->stories()->sum('estimated_hours');
    }

    /**
     * Completed hours (stories in done status)
     */
    public function getCompletedHoursAttribute(): float
    {
        return $this->stories()->where('status', 'done')->sum('estimated_hours');
    }
}
