<?php

use App\Console\Commands\UpdateExchangeRatesCommand;
use App\Jobs\UpdateExchangeRatesJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->daily();


Schedule::command(UpdateExchangeRatesCommand::class)->daily();
