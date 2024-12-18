<?php

namespace App\Filament\Widgets;

use App\Models\Business;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CombinedOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Business', Business::query()->count()),
            Stat::make('Total Users', User::query()->count()),

        ];
    }
}
