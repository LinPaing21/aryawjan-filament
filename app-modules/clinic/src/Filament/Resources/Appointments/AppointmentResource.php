<?php

namespace Stella\Clinic\Filament\Resources\Appointments;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Stella\Clinic\Filament\Resources\Appointments\Pages\CreateAppointment;
use Stella\Clinic\Filament\Resources\Appointments\Pages\EditAppointment;
use Stella\Clinic\Filament\Resources\Appointments\Pages\ListAppointments;
use Stella\Clinic\Filament\Resources\Appointments\Pages\ViewAppointment;
use Stella\Clinic\Filament\Resources\Appointments\Schemas\AppointmentForm;
use Stella\Clinic\Filament\Resources\Appointments\Schemas\AppointmentInfolist;
use Stella\Clinic\Filament\Resources\Appointments\Tables\AppointmentsTable;
use Illuminate\Database\Eloquent\Builder;
use Stella\Clinic\Models\Appointment;
use Stella\Clinic\Services\AppointmentService;
use UnitEnum;

class AppointmentResource extends Resource
{
    protected static ?string $model = Appointment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::CalendarDays;

    protected static string|UnitEnum|null $navigationGroup = 'Clinic';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'token_number';

    public static function canAccess(): bool
    {
        return auth()->user()->canAny(['manage appointments', 'view appointments']);
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
        return AppointmentForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AppointmentInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AppointmentsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAppointments::route('/'),
            // 'create' => CreateAppointment::route('/create'),
            'view' => ViewAppointment::route('/{record}'),
            'edit' => EditAppointment::route('/{record}/edit'),
        ];
    }
}
