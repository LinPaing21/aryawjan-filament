<?php

namespace Stella\Pharmacy\Filament\Resources\Medicines\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class MedicineInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name'),
                TextEntry::make('description')
                    ->columnSpanFull(),
                TextEntry::make('original_price')
                    ->money('USD'),
                TextEntry::make('sell_price')
                    ->money('USD'),
                TextEntry::make('stock_quantity')
                    ->numeric(),
                IconEntry::make('is_prescription_only')
                    ->boolean(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
