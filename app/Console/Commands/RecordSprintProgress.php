<?php

namespace App\Console\Commands;

use App\Models\Sprint;
use App\Models\SprintDailyProgress;
use Illuminate\Console\Command;

class RecordSprintProgress extends Command
{
    protected $signature = 'sprints:record-progress';

    protected $description = 'Record daily progress for all active sprints (for burndown charts)';

    public function handle(): int
    {
        $activeSprints = Sprint::where('status', 'active')->get();

        if ($activeSprints->isEmpty()) {
            $this->info('No active sprints found.');
            return self::SUCCESS;
        }

        $this->info("Recording progress for {$activeSprints->count()} active sprint(s)...");

        foreach ($activeSprints as $sprint) {
            SprintDailyProgress::recordProgress($sprint);
            $this->line("- Recorded progress for: {$sprint->name}");
        }

        $this->info('Done!');
        return self::SUCCESS;
    }
}
