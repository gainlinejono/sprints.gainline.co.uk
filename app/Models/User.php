<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'google_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'google_id',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Check if user belongs to gainline.co.uk domain
     */
    public function isGainlineUser(): bool
    {
        return str_ends_with($this->email, '@gainline.co.uk');
    }

    /**
     * Projects the user is a member of
     */
    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'project_members')
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * Stories assigned to this user
     */
    public function assignedStories(): HasMany
    {
        return $this->hasMany(Story::class, 'assignee_id');
    }

    /**
     * Stories reported by this user
     */
    public function reportedStories(): HasMany
    {
        return $this->hasMany(Story::class, 'reporter_id');
    }

    /**
     * Tasks assigned to this user
     */
    public function assignedTasks(): HasMany
    {
        return $this->hasMany(Task::class, 'assignee_id');
    }

    /**
     * Time logs created by this user
     */
    public function timeLogs(): HasMany
    {
        return $this->hasMany(TimeLog::class);
    }

    /**
     * Comments created by this user
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }
}
