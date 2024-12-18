<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Illuminate\Support\Carbon;

class UsersChart extends ChartWidget
{
    protected static ?int $sort = 3;

    protected static ?string $heading = 'User Chart';

    // Default selected filter
    public ?string $filter = 'month'; // Default filter

    // Define available filters
    protected function getFilters(): ?array
    {
        return [
            'today' => 'Today',
            'week' => 'Last Week',
            'month' => 'Last Month',
            'year' => 'This Year',
        ];
    }

    // Fetch chart data dynamically based on filter
    protected function getData(): array
    {
        $startDate = match ($this->filter) {
            'today' => Carbon::today(),
            'week' => Carbon::now()->subWeek(),
            'month' => Carbon::now()->subMonth(),
            'year' => Carbon::now()->subYear(),
            default => Carbon::now()->subYear(),
        };

        $endDate = Carbon::now();

        $data = Trend::model(User::class)
            ->between(start: $startDate, end: $endDate)
            ->{$this->getTrendGroupingMethod($this->filter)}()
            ->count();

        return [
            'datasets' => [
                [
                    'label' => 'Users',
                    'data' => $data->map(fn(TrendValue $value) => $value->aggregate),
                ],
            ],
            'labels' => $data->map(fn(TrendValue $value) => $value->date),


        ];
    }

    protected function getTrendGroupingMethod(string $filter): string
    {
        return match ($filter) {
            'today' => 'perHour',
            'week' => 'perDay',
            'month' => 'perDay',
            'year' => 'perMonth',
            default => 'perMonth',
        };
    }



    // Define the type of chart
    protected function getType(): string
    {
        return 'line';
    }
}
