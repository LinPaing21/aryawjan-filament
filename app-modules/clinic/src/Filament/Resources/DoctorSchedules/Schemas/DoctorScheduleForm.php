<?php

namespace Stella\Clinic\Filament\Resources\DoctorSchedules\Schemas;

use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Schema;

class DoctorScheduleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('doctor_id')
                    ->relationship('doctor', 'name', fn ($query) => $query->whereRole('Doctor'))
                    ->required(),
                CheckboxList::make('days')
                    ->options([
                        'Mon' => 'Mon',
                        'Tue' => 'Tue',
                        'Wed' => 'Wed',
                        'Thu' => 'Thu',
                        'Fri' => 'Fri',
                        'Sat' => 'Sat',
                        'Sun' => 'Sun',
                    ])
                    ->columns(4)
                    ->required(),
                TimePicker::make('start_time')
                    ->native(false)
                    ->seconds(false)
                    ->displayFormat('h:i a')
                    ->required(),
                TimePicker::make('end_time')
                    ->native(false)
                    ->seconds(false)
                    ->displayFormat('h:i a')
                    ->required(),
                TextInput::make('max_patients')
                    ->required()
                    ->numeric()
                    ->default(20),
            ]);
    }
}
