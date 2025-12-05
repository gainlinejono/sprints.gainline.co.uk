<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // This table stores daily snapshots for burndown chart calculation
        Schema::create('sprint_daily_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sprint_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->decimal('total_hours', 10, 2)->default(0);
            $table->decimal('completed_hours', 10, 2)->default(0);
            $table->decimal('remaining_hours', 10, 2)->default(0);
            $table->integer('total_stories')->default(0);
            $table->integer('completed_stories')->default(0);
            $table->integer('total_tasks')->default(0);
            $table->integer('completed_tasks')->default(0);
            $table->timestamps();

            $table->unique(['sprint_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sprint_daily_progress');
    }
};
