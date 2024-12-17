<?php

namespace App\Filament\Widgets;

use App\Models\Business;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class BusinessOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Business', Business::query()->count()),
        ];
    }
}
