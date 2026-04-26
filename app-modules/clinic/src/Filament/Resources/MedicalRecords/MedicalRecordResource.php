<?php

namespace Stella\Clinic\Filament\Resources\MedicalRecords;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Stella\Clinic\Filament\Resources\MedicalRecords\Pages\CreateMedicalRecord;
use Stella\Clinic\Filament\Resources\MedicalRecords\Pages\EditMedicalRecord;
use Stella\Clinic\Filament\Resources\MedicalRecords\Pages\ListMedicalRecords;
use Stella\Clinic\Filament\Resources\MedicalRecords\Pages\ViewMedicalRecord;
use Stella\Clinic\Filament\Resources\MedicalRecords\RelationManagers\InvoicesRelationManager;
use Stella\Clinic\Filament\Resources\MedicalRecords\Schemas\MedicalRecordForm;
use Stella\Clinic\Filament\Resources\MedicalRecords\Schemas\MedicalRecordInfolist;
use Stella\Clinic\Filament\Resources\MedicalRecords\Tables\MedicalRecordsTable;
use Illuminate\Database\Eloquent\Builder;
use Stella\Clinic\Models\MedicalRecord;
use UnitEnum;

class MedicalRecordResource extends Resource
{
    protected static ?string $model = MedicalRecord::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|UnitEnum|null $navigationGroup = 'Clinic';

    protected static ?int $navigationSort = 2;
    public static function canAccess(): bool
    {
        return auth()->user()->canAny(['manage medical records', 'view medical records']);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (auth()->user()->hasRole('Doctor')) {
            $query->where('doctor_id', auth()->id());
        }

        return $query;
    }

    public static function form(Schema $schema): Schema
    {
        return MedicalRecordForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return MedicalRecordInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MedicalRecordsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            InvoicesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMedicalRecords::route('/'),
            'create' => CreateMedicalRecord::route('/create'),
            'view' => ViewMedicalRecord::route('/{record}'),
            'edit' => EditMedicalRecord::route('/{record}/edit'),
        ];
    }
}
