<?php

namespace Stella\Ai\Filament\Resources\TriageLogs;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Stella\Ai\Filament\Resources\TriageLogs\Pages\CreateTriageLog;
use Stella\Ai\Filament\Resources\TriageLogs\Pages\EditTriageLog;
use Stella\Ai\Filament\Resources\TriageLogs\Pages\ListTriageLogs;
use Stella\Ai\Filament\Resources\TriageLogs\Schemas\TriageLogForm;
use Stella\Ai\Filament\Resources\TriageLogs\Tables\TriageLogsTable;
use Stella\Ai\Models\TriageLog;
use UnitEnum;

class TriageLogResource extends Resource
{
    protected static ?string $model = TriageLog::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::DocumentMagnifyingGlass;

    protected static string|UnitEnum|null $navigationGroup = 'Clinic';

    protected static ?int $navigationSort = 2;

    public static function canAccess(): bool
    {
        return auth()->user()->canAny(['manage triage logs', 'view triage logs']);
    }

    public static function form(Schema $schema): Schema
    {
        return TriageLogForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TriageLogsTable::configure($table);
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
            'index' => ListTriageLogs::route('/'),
            'create' => CreateTriageLog::route('/create'),
            'edit' => EditTriageLog::route('/{record}/edit'),
        ];
    }
}
