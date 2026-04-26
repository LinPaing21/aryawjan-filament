<?php

namespace Stella\Clinic\Filament\Resources\DoctorSchedules;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Stella\Clinic\Filament\Resources\DoctorSchedules\Pages\CreateDoctorSchedule;
use Stella\Clinic\Filament\Resources\DoctorSchedules\Pages\EditDoctorSchedule;
use Stella\Clinic\Filament\Resources\DoctorSchedules\Pages\ListDoctorSchedules;
use Stella\Clinic\Filament\Resources\DoctorSchedules\Pages\ViewDoctorSchedule;
use Stella\Clinic\Filament\Resources\DoctorSchedules\Schemas\DoctorScheduleForm;
use Stella\Clinic\Filament\Resources\DoctorSchedules\Schemas\DoctorScheduleInfolist;
use Stella\Clinic\Filament\Resources\DoctorSchedules\Tables\DoctorSchedulesTable;
use Illuminate\Database\Eloquent\Builder;
use Stella\Clinic\Models\DoctorSchedule;
use UnitEnum;

class DoctorScheduleResource extends Resource
{
    protected static ?string $model = DoctorSchedule::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Calendar;

    protected static ?string $recordTitleAttribute = 'Doctor Schedule';

    protected static string|UnitEnum|null $navigationGroup = 'Clinic';

    protected static ?int $navigationSort = 2;

    public static function canAccess(): bool
    {
        return auth()->user()->canAny(['manage doctor schedules', 'view doctor schedules']);
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
        return DoctorScheduleForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return DoctorScheduleInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DoctorSchedulesTable::configure($table);
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
            'index' => ListDoctorSchedules::route('/'),
            'create' => CreateDoctorSchedule::route('/create'),
            'view' => ViewDoctorSchedule::route('/{record}'),
            'edit' => EditDoctorSchedule::route('/{record}/edit'),
        ];
    }
}
