<?php

namespace App\Filament\Resources\BusinessResource\Pages;

use App\Filament\Resources\BusinessResource;
use App\Models\Paddle\SubscriptionTransaction;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;

class ViewTransactions extends Page
{
    use InteractsWithRecord;

    protected static string $resource = BusinessResource::class;

    protected static string $view = 'filament.resources.business-resource.pages.view-transactions';

    public function mount(int | string $record): void
    {
        $this->record = $this->resolveRecord($record);

        // dd($this->record);
    }

    
    public function table(Table $table): Table
    {

        dd(SubscriptionTransaction::where('billable_id', $this->record->id)
                          ->where('billable_type', 'App\Models\Business')
                          ->get());

        return $table
            ->query(fn ($query) => SubscriptionTransaction::where('billable_id', $this->record->id)
                                        ->where('billable_type', 'App\Models\Business'))
            ->columns([
                TextColumn::make('id')->label('Transaction ID'),
                TextColumn::make('total')->label('Amount'),
                TextColumn::make('status')->label('Status'),
                TextColumn::make('created_at')->label('Date')->dateTime(),
                TextColumn::make('invoice_number')->label('Invoice Number'),
                TextColumn::make('currency')->label('Currency'),
                TextColumn::make('paddle_subscription_id')->label('Paddle Subscription ID'),
                TextColumn::make('tax')->label('Tax'),
                TextColumn::make('billed_at')->label('Billed At')->dateTime(),
            ])
            ->filters([]);
    }
    
}
