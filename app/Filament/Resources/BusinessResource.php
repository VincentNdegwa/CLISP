<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BusinessResource\Pages;
use App\Filament\Resources\BusinessResource\RelationManagers;
use App\Filament\Resources\BusinessResource\RelationManagers\BusinessUserRelationManager;
use App\Filament\Resources\BusinessResource\RelationManagers\SubscriptionsRelationManager;
use App\Models\Business;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;

class BusinessResource extends Resource
{
    protected static ?string $model = Business::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        $currencies = include(base_path('resources/js/currency.php'));
        $currencyList = [];
        foreach ($currencies as $code => $details) {
            $currencyList[$details['code']] = "{$details['name']} ({$details['code']})";
        }

        return $form
            ->schema([
                TextInput::make("business_name")->required()->maxLength(100),
                TextInput::make('email')->maxLength(100)->required(),
                TextInput::make('phone_number')->maxLength(100)->required(),
                Select::make('currency_code')->options($currencyList)->required()->searchable()->preload(),

                TextInput::make('location')->maxLength(100)->required(),
                TextInput::make('website')->maxLength(100),
                FileUpload::make('logo'),
                TextInput::make('registration_number')->maxLength(100),
                Select::make('business_type_id')
                    ->options(DB::table('business_types')
                        ->pluck('name', 'id')->toArray())
                    ->label('Business Type'),
                Select::make('industry_id')
                    ->options(DB::table('industries')
                        ->pluck('name', 'id')->toArray())
                    ->label('Industry Type'),
                DatePicker::make('date_registered'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns(components: [
                TextColumn::make('business_name')->searchable(),
                ImageColumn::make('logo')->size(40)->circular(),
                TextColumn::make('location')->searchable(),
                TextColumn::make('currency_code')->searchable(),
                TextColumn::make('phone_number')->searchable(),
                TextColumn::make('email')->searchable(),
                TextColumn::make('registration_number')->searchable(),

            ])
            ->filters([
                SelectFilter::make('business_type_id')
                    ->options(DB::table('business_types')
                        ->pluck('name', 'id')->toArray())
                    ->label('Business Type'),
                SelectFilter::make('industry_id')
                    ->options(DB::table('industries')
                        ->pluck('name', 'id')->toArray())
                    ->label('Industry Type'),
                SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                    ])
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),

            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            BusinessUserRelationManager::class,
            SubscriptionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBusinesses::route('/'),
            'create' => Pages\CreateBusiness::route('/create'),
            'edit' => Pages\EditBusiness::route('/{record}/edit'),
            'transactions' => Pages\ViewTransactions::route('/{record}/transactions')
        ];
    }
}
