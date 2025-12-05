<?php

use Illuminate\Support\Facades\Schedule;

// Record daily sprint progress at midnight
Schedule::command('sprints:record-progress')->dailyAt('00:00');
