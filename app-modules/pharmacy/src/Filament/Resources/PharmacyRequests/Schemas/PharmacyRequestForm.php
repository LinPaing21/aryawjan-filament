<?php

namespace Stella\Pharmacy\Filament\Resources\PharmacyRequests\Schemas;

use Filament\Actions\Action;
use Filament\Forms\Components\Actions\Action as FormAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class PharmacyRequestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('triage_log_id')
                    ->relationship('triageLog', 'id')
                    ->searchable()
                    ->required(),
                Select::make('patient_id')
                    ->relationship('patient', 'name')
                    ->searchable()
                    ->required(),
                Select::make('pharmacist_id')
                    ->relationship('pharmacist', 'name')
                    ->searchable(),
                TagsInput::make('suggested_medicines')
                    ->columnSpanFull()
                    ->disabled()
                    ->live(),
                TagsInput::make('approved_medicines')
                    ->columnSpanFull()
                    ->suggestions(fn (Get $get) => $get('suggested_medicines') ?? [])
                    ->hintAction(
                        Action::make('copy_all')
                            ->label('Copy All Suggested')
                            ->icon('heroicon-m-clipboard-document-list')
                            ->action(function (Set $set, Get $get) {
                                $set('approved_medicines', $get('suggested_medicines') ?? []);
                            })
                    )
                    ->afterStateHydrated(function (Set $set, Get $get) {
                            $approved = $get('approved_medicines');
                            if (empty($approved)) {
                                $approved = $get('suggested_medicines') ?? [];
                            }

                            $repeaterData = [];
                            foreach ($approved as $drug) {
                                $repeaterData[(string) Str::uuid()] = [
                                    'drug_name' => $drug,
                                    'dosage' => null,
                                    'is_available_in_clinic' => true,
                                    'unit_price' => null,
                                    'quantity' => 1,
                                ];
                            }
                            $set('prescriptions', $repeaterData);
                    })
                    ->live(),
                Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                        'completed' => 'Completed',
                    ])
                    ->default('pending')
                    ->live()
                    ->required(),
                Repeater::make('prescriptions')
                    ->visible(fn (Get $get) => $get('status') === 'approved')
                    ->required(fn (Get $get) => $get('status') === 'approved')
                    ->dehydrated(false)
                    ->schema([
                        TextInput::make('drug_name')->required(),
                        TextInput::make('dosage'),
                        Toggle::make('is_available_in_clinic')
                            ->label('Available in Clinic')
                            ->default(true)
                            ->live(),

                        TextInput::make('unit_price')
                            ->numeric()
                            ->hidden(fn ($get) => !$get('is_available_in_clinic')),

                        TextInput::make('quantity')
                            ->numeric()
                            ->hidden(fn ($get) => !$get('is_available_in_clinic')),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
