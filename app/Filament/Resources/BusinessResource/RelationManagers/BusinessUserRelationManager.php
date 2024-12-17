<?php

namespace App\Filament\Resources\BusinessResource\RelationManagers;

use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BusinessUserRelationManager extends RelationManager
{
    protected static string $relationship = 'business_user';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->getOptionLabelUsing(
                        fn($value) => User::find($value)?->name . ' (' . User::find($value)?->email . ')'
                    )->searchable()
                    ->preload()
                    ->createOptionForm([
                        TextInput::make('name')->required(),
                        TextInput::make('email')->unique()->required(),
                        TextInput::make('password')->password()->required(),

                    ])
                    ->required(),
                Forms\Components\Select::make('business_id')
                    ->relationship('business', 'business_name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('role')->options([
                    "Owner" => 'Owner',
                    "Admin" => 'Admin',
                    'Worker' => 'Worker',
                ])
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('user.name')
            ->columns([
                Tables\Columns\TextColumn::make('user.name')->label('User Name'),
                Tables\Columns\TextColumn::make('user.email')->label('User Email'),
                Tables\Columns\ImageColumn::make('user.profile_image')->label('User Profile Image'),
                Tables\Columns\TextColumn::make('role')->label('Role'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()


            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->visible(fn($record) => $record->role != 'Owner'),
                Tables\Actions\DeleteAction::make()->visible(fn($record) => $record->role != 'Owner'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
