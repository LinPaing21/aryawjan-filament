<?php

namespace Stella\Users\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Role;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email(),
                TextInput::make('phone')
                    ->label('Phone Number')
                    ->required(),
                DateTimePicker::make('email_verified_at')->native(false),
                TextInput::make('password')
                    ->password()
                    ->revealable()
                    ->visibleOn('create')
                    ->required(),
                Select::make('roles')
                    ->multiple()
                    ->relationship(titleAttribute: 'name')
                    ->placeholder('Select roles')
                    ->live()
                    ->searchable(false),
                Group::make()
                    ->relationship('doctorProfile')
                    ->schema([
                        TextInput::make('specialization')->required(),
                        TextInput::make('degree')->required(),
                        TextInput::make('university')->required(),
                        TextInput::make('license_no')->required(),
                        Textarea::make('biography')->nullable()->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->columnSpanFull()
                    ->visible(function (Get $get, ?Model $record) {
                        if ($record && $record->hasRole('Doctor')) {
                            return true;
                        }
                        if ($record && $record->hasRole('doctor')) {
                            return true;
                        }

                        $roleIds = $get('roles') ?? [];
                        $doctorRole = Role::whereIn('name', ['Doctor', 'doctor'])->pluck('id')->toArray();

                        return count(array_intersect($roleIds, $doctorRole)) > 0;
                    }),
                                CheckboxList::make('permissions')
                    ->relationship(titleAttribute: 'name')
                    ->columns(['default' => 1, 'sm' => 2, 'lg' => 3, 'xl' => 4])
                    ->searchable()
                    ->bulkToggleable()
                    ->columnSpanFull(),
            ]);
    }
}

