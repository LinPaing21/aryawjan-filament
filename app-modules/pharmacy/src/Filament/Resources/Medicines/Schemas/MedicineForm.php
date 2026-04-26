<?php

namespace Stella\Pharmacy\Filament\Resources\Medicines\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class MedicineForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Textarea::make('description')
                    ->columnSpanFull(),
                TextInput::make('original_price')
                    ->numeric()
                    ->prefix('$')
                    ->required(),
                TextInput::make('sell_price')
                    ->numeric()
                    ->prefix('$')
                    ->required(),
                TextInput::make('stock_quantity')
                    ->numeric()
                    ->default(0)
                    ->required(),
                Toggle::make('is_prescription_only')
                    ->required(),
            ]);
    }
}
