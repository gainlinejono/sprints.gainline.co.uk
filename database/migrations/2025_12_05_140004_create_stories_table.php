<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('epic_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('sprint_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('acceptance_criteria')->nullable();
            $table->decimal('estimated_hours', 8, 2)->default(0);
            $table->enum('priority', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->enum('status', ['backlog', 'todo', 'in_progress', 'review', 'done'])->default('backlog');
            $table->integer('order')->default(0);
            $table->string('story_key')->unique(); // Like "PROJ-123"
            $table->foreignId('assignee_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('reporter_id')->constrained('users');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['project_id', 'status']);
            $table->index(['sprint_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stories');
    }
};
