<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SprintDailyProgress extends Model
{
    use HasFactory;

    protected $table = 'sprint_daily_progress';

    protected $fillable = [
        'sprint_id',
        'date',
        'total_hours',
        'completed_hours',
        'remaining_hours',
        'total_stories',
        'completed_stories',
        'total_tasks',
        'completed_tasks',
    ];

    protected $casts = [
        'date' => 'date',
        'total_hours' => 'decimal:2',
        'completed_hours' => 'decimal:2',
        'remaining_hours' => 'decimal:2',
    ];

    /**
     * The sprint this progress belongs to
     */
    public function sprint(): BelongsTo
    {
        return $this->belongsTo(Sprint::class);
    }

    /**
     * Record daily progress for a sprint
     */
    public static function recordProgress(Sprint $sprint): self
    {
        $stories = $sprint->stories;
        $tasks = $stories->flatMap->tasks;

        return self::updateOrCreate(
            [
                'sprint_id' => $sprint->id,
                'date' => now()->toDateString(),
            ],
            [
                'total_hours' => $stories->sum('estimated_hours'),
                'completed_hours' => $stories->where('status', 'done')->sum('estimated_hours'),
                'remaining_hours' => $stories->where('status', '!=', 'done')->sum('estimated_hours'),
                'total_stories' => $stories->count(),
                'completed_stories' => $stories->where('status', 'done')->count(),
                'total_tasks' => $tasks->count(),
                'completed_tasks' => $tasks->where('status', 'done')->count(),
            ]
        );
    }
}
