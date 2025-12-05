<?php

use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EpicController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\SprintController;
use App\Http\Controllers\StoryController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return Inertia::render('Auth/Login');
})->name('login');

// Google OAuth routes
Route::prefix('auth/google')->group(function () {
    Route::get('redirect', [GoogleAuthController::class, 'redirect'])->name('auth.google');
    Route::get('callback', [GoogleAuthController::class, 'callback'])->name('auth.google.callback');
});

Route::post('logout', [GoogleAuthController::class, 'logout'])->name('logout')->middleware('auth');

// Protected routes
Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Projects
    Route::resource('projects', ProjectController::class);
    Route::prefix('projects/{project}')->group(function () {
        Route::get('members', [ProjectController::class, 'members'])->name('projects.members');
        Route::post('members', [ProjectController::class, 'addMember'])->name('projects.members.add');
        Route::delete('members/{user}', [ProjectController::class, 'removeMember'])->name('projects.members.remove');

        // Sprints
        Route::resource('sprints', SprintController::class);
        Route::prefix('sprints/{sprint}')->group(function () {
            Route::get('board', [SprintController::class, 'board'])->name('sprints.board');
            Route::get('timeline', [SprintController::class, 'timeline'])->name('sprints.timeline');
            Route::get('reports', [SprintController::class, 'reports'])->name('sprints.reports');
            Route::post('start', [SprintController::class, 'start'])->name('sprints.start');
            Route::post('complete', [SprintController::class, 'complete'])->name('sprints.complete');
        });

        // Epics
        Route::resource('epics', EpicController::class);

        // Stories
        Route::resource('stories', StoryController::class);
        Route::prefix('stories/{story}')->group(function () {
            Route::patch('status', [StoryController::class, 'updateStatus'])->name('stories.status');
            Route::post('move-to-sprint', [StoryController::class, 'moveToSprint'])->name('stories.move-to-sprint');
            Route::post('comments', [StoryController::class, 'addComment'])->name('stories.comments.add');

            // Tasks
            Route::post('tasks', [TaskController::class, 'store'])->name('tasks.store');
            Route::put('tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');
            Route::delete('tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');
            Route::patch('tasks/{task}/status', [TaskController::class, 'updateStatus'])->name('tasks.status');
            Route::post('tasks/{task}/log-time', [TaskController::class, 'logTime'])->name('tasks.log-time');
            Route::post('tasks/reorder', [TaskController::class, 'reorder'])->name('tasks.reorder');
        });
        Route::post('stories/reorder', [StoryController::class, 'reorder'])->name('stories.reorder');
    });
});
