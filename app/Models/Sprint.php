<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Sprint extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'project_id',
        'name',
        'goal',
        'start_date',
        'end_date',
        'status',
        'sprint_number',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    protected $appends = [
        'total_hours',
        'completed_hours',
        'remaining_hours',
        'days_remaining',
        'progress_percentage',
    ];

    /**
     * Boot the model
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($sprint) {
            if (!$sprint->sprint_number) {
                $sprint->sprint_number = $sprint->project->getNextSprintNumber();
            }
        });
    }

    /**
     * The project this sprint belongs to
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Stories in this sprint
     */
    public function stories(): HasMany
    {
        return $this->hasMany(Story::class);
    }

    /**
     * Daily progress snapshots
     */
    public function dailyProgress(): HasMany
    {
        return $this->hasMany(SprintDailyProgress::class);
    }

    /**
     * Total estimated hours for sprint stories
     */
    public function getTotalHoursAttribute(): float
    {
        // Use loaded relationship if available, otherwise query
        if ($this->relationLoaded('stories')) {
            return $this->stories->sum('estimated_hours');
        }
        return $this->stories()->sum('estimated_hours');
    }

    /**
     * Completed hours (done stories)
     */
    public function getCompletedHoursAttribute(): float
    {
        // Use loaded relationship if available, otherwise query
        if ($this->relationLoaded('stories')) {
            return $this->stories->where('status', 'done')->sum('estimated_hours');
        }
        return $this->stories()->where('status', 'done')->sum('estimated_hours');
    }

    /**
     * Remaining hours
     */
    public function getRemainingHoursAttribute(): float
    {
        return $this->total_hours - $this->completed_hours;
    }

    /**
     * Days remaining in sprint
     */
    public function getDaysRemainingAttribute(): int
    {
        if ($this->status !== 'active') {
            return 0;
        }
        return max(0, Carbon::now()->startOfDay()->diffInDays($this->end_date, false));
    }

    /**
     * Progress percentage
     */
    public function getProgressPercentageAttribute(): float
    {
        // 1. Cast to float to handle strings ("0"), nulls, or integers safely.
        $total = (float) $this->total_hours;
        $completed = (float) $this->completed_hours;

        // 2. Check if total is zero (or negative) to avoid division errors.
        if ($total <= 0) {
            return 0;
        }

        // 3. Perform the calculation.
        return round(($completed / $total) * 100, 1);
    }

    /**
     * Check if sprint is on track
     */
    public function isOnTrack(): bool
    {
        if ($this->status !== 'active' || $this->total_hours === 0) {
            return true;
        }

        $totalDays = $this->start_date->diffInDays($this->end_date);
        $daysElapsed = $this->start_date->diffInDays(Carbon::now());

        if ($totalDays === 0) {
            return true;
        }

        $expectedProgress = ($daysElapsed / $totalDays) * 100;
        return $this->progress_percentage >= $expectedProgress - 10; // 10% tolerance
    }

    /**
     * Get burndown data
     */
    public function getBurndownData(): array
    {
        $data = [];
        $startDate = $this->start_date->copy();
        $endDate = $this->end_date->copy();
        $totalDays = $startDate->diffInDays($endDate);
        $totalHours = $this->total_hours;

        $dailyIdealBurn = $totalDays > 0 ? $totalHours / $totalDays : 0;

        $dailyProgress = $this->dailyProgress()
            ->orderBy('date')
            ->get()
            ->keyBy(fn ($p) => $p->date->format('Y-m-d'));

        $currentDate = $startDate->copy();
        $dayNumber = 0;

        while ($currentDate <= $endDate) {
            $dateKey = $currentDate->format('Y-m-d');
            $idealRemaining = max(0, $totalHours - ($dailyIdealBurn * $dayNumber));

            $actualRemaining = $dailyProgress->has($dateKey)
                ? $dailyProgress[$dateKey]->remaining_hours
                : ($currentDate <= Carbon::now() ? $this->remaining_hours : null);

            $data[] = [
                'date' => $dateKey,
                'ideal_remaining' => round($idealRemaining, 2),
                'actual_remaining' => $actualRemaining !== null ? round($actualRemaining, 2) : null,
            ];

            $currentDate->addDay();
            $dayNumber++;
        }

        return $data;
    }
}
