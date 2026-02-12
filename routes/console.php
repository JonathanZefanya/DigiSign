<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule monthly quota reset (runs on the 1st of each month at 00:00)
Schedule::command('quotas:reset-monthly')
    ->monthly()
    ->at('00:00')
    ->timezone(config('app.timezone'));
