<?php

namespace App\Console\Commands;

use App\Http\Controllers\ExchangeRatesController;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class UpdateExchangeRatesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-exchange-rates-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $newCuurencyController = new ExchangeRatesController();
        $newCuurencyController->getRates();
    }
}
