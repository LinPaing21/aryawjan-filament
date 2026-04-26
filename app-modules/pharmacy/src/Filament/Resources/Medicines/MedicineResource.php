<?php

namespace Stella\Pharmacy\Filament\Resources\Medicines;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Stella\Pharmacy\Filament\Resources\Medicines\Pages\CreateMedicine;
use Stella\Pharmacy\Filament\Resources\Medicines\Pages\EditMedicine;
use Stella\Pharmacy\Filament\Resources\Medicines\Pages\ListMedicines;
use Stella\Pharmacy\Filament\Resources\Medicines\Pages\ViewMedicine;
use Stella\Pharmacy\Filament\Resources\Medicines\Schemas\MedicineForm;
use Stella\Pharmacy\Filament\Resources\Medicines\Schemas\MedicineInfolist;
use Stella\Pharmacy\Filament\Resources\Medicines\Tables\MedicinesTable;
use Stella\Pharmacy\Models\Medicine;
use UnitEnum;

class MedicineResource extends Resource
{
    protected static ?string $model = Medicine::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Beaker;

    protected static string|UnitEnum|null $navigationGroup = 'Pharmacy';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?int $navigationSort = 3;

    public static function canAccess(): bool
    {
        return auth()->user()->canAny(['manage inventory', 'view inventory']);
    }

    public static function form(Schema $schema): Schema
    {
        return MedicineForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return MedicineInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MedicinesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMedicines::route('/'),
            'create' => CreateMedicine::route('/create'),
            'view' => ViewMedicine::route('/{record}'),
            'edit' => EditMedicine::route('/{record}/edit'),
        ];
    }
}
