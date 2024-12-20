<?php

namespace App\Filament\Widgets;

use App\Models\Business;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class BusinessChart extends ChartWidget
{
    protected static ?string $heading = 'Businesses';
    protected static ?int $sort = 3;

    // protected int | string | array $columnSpan = [
    //     'md' => 2,
    //     'xl' => 2,
    // ];

    protected function getData(): array
    {
        $data = Trend::model(Business::class)
            ->between(start: now()->subYear(), end: now())
            ->perMonth()
            ->count();

        return [

            'datasets' => [
                [
                    'label' => 'Businesses',
                    'data' => $data->map(fn(TrendValue $value) => $value->aggregate),
                ],
            ],
            'labels' => $data->map(fn(TrendValue $value) => $value->date),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
