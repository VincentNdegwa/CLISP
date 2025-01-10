<?php

namespace App\Filament\Resources\BusinessResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SubscriptionsRelationManager extends RelationManager
{
    protected static string $relationship = 'subscriptions';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('business_id')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('business_id')
            ->columns([
                Tables\Columns\TextColumn::make('type'),
                Tables\Columns\TextColumn::make('status'),
                Tables\Columns\TextColumn::make('trial_ends_at')
                    ->dateTime()
                    ->placeholder("--"),
                Tables\Columns\TextColumn::make('paused_at')
                    ->dateTime()
                    ->placeholder("--"),
                Tables\Columns\TextColumn::make('ends_at')
                    ->dateTime()
                    ->placeholder("--"),
                Tables\Columns\TextColumn::make('is_subscribed_to_default')
                    ->label('Subscribed to Default')
                    ->getStateUsing(function () {
                        $business = $this->ownerRecord;
                        return $business?->subscribed('default') ? 'True' :  'False';
                    })
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
                // Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('viewTransactions')
                ->label('View Transactions')
                ->url(fn ($record) => route('filament.admin.resources.businesses.transactions', ['record' => $record]))
               

            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
