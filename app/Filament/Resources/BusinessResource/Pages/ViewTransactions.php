<?php

namespace App\Filament\Resources\BusinessResource\Pages;

use App\Filament\Resources\BusinessResource;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;

class ViewTransactions extends Page
{
    use InteractsWithRecord;

    protected static string $resource = BusinessResource::class;

    protected static string $view = 'filament.resources.business-resource.pages.view-transactions';

    public function mount(int | string $record): void
    {
        $this->record = $this->resolveRecord($record);
    }
}
