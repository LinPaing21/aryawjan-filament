<?php

namespace Stella\Ai\Filament\Resources\TriageLogs\Tables;

use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Actions\Action as FormAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Icon;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Stella\Ai\Enums\TriageStatus;
use Stella\Ai\Models\TriageLog;
use Stella\Clinic\Models\Appointment;
use Stella\Clinic\Services\AppointmentService;
use Stella\Pharmacy\Models\PharmacyRequest;

class TriageLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('patient.name')
                    ->searchable(),
                TextColumn::make('attachments')
                    ->badge()
                    ->state(function (TriageLog $record) {
                        $count = count((array) $record->attachments);
                        return $count > 0 ? "{$count} File(s)" : null;
                    }),
                TextColumn::make('raw_symptoms')
                    ->searchable()
                    ->limit(50),
                TextColumn::make('ai_analysis.reasoning')
                    ->searchable()
                    ->limit(50),
                TextColumn::make('severity')
                    ->badge()
                    ->searchable(),
                TextColumn::make('status')
                    ->badge()
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                Action::make('create_appointment')
                    ->label('Create Appointment')
                    ->icon('heroicon-o-calendar-days')
                    ->color('primary')
                    ->visible(fn (TriageLog $record) => $record->status === TriageStatus::TO_DOCTOR && !$record->appointment)
                    ->form([
                        Select::make('doctor_id')
                            ->label('Assign Doctor')
                            ->options(User::whereRole('Doctor')->pluck('name', 'id'))
                            ->searchable()
                            ->live()
                            ->required(),
                        DatePicker::make('scheduled_at')
                            ->native(false)
                            ->closeOnDateSelection()
                            ->minDate(today())
                            ->maxDate(today()->addMonth())
                            ->required()
                            ->disabled(fn (Get $get) => !$get('doctor_id'))
                            ->disabledDates(function (Get $get, AppointmentService $appointmentService) {
                                $doctorId = $get('doctor_id');
                                return $appointmentService->getUnavailableDates($doctorId);
                            })
                            ->belowContent([
                                Icon::make('heroicon-o-information-circle')
                                    ->color('primary'),
                                'Please select doctor to see available dates.',
                            ]),
                        Select::make('status')
                            ->options([
                                'booked' => 'Booked',
                                'completed' => 'Completed',
                                'cancelled' => 'Cancelled',
                            ])
                            ->default('booked')
                            ->required(),
                    ])
                    ->action(function (array $data, TriageLog $record, AppointmentService $appointmentService) {
                        Appointment::create([
                            'patient_id' => $record->patient_id,
                            'triage_log_id' => $record->id,
                            'doctor_id' => $data['doctor_id'],
                            'token_number' => $appointmentService->calculateTokenNumber($data['scheduled_at'], $data['doctor_id']),
                            'scheduled_at' => $data['scheduled_at'],
                            'status' => $data['status'],
                        ]);

                        Notification::make()
                            ->title('Appointment created successfully')
                            ->success()
                            ->send();
                    }),

                Action::make('create_pharmacy_request')
                    ->label('Send to Pharmacy')
                    ->icon('heroicon-o-beaker')
                    ->color('success')
                    ->visible(fn (TriageLog $record) => $record->status === TriageStatus::TO_PHARMACY && !$record->pharmacyRequest)
                    ->form([
                        Select::make('pharmacist_id')
                            ->label('Assign Pharmacist (Optional)')
                            ->options(User::whereRole('Pharmacist')->pluck('name', 'id'))
                            ->searchable(),
                        TagsInput::make('suggested_medicines')
                            ->default(fn (TriageLog $record) => $record->ai_analysis['suggested_meds'] ?? [])
                            ->columnSpanFull()
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
                            ),
                        Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'approved' => 'Approved',
                                'rejected' => 'Rejected',
                                'completed' => 'Completed',
                            ])
                            ->default('pending')
                            ->required(),
                    ])
                    ->action(function (array $data, TriageLog $record) {
                        PharmacyRequest::create([
                            'patient_id' => $record->patient_id,
                            'triage_log_id' => $record->id,
                            'pharmacist_id' => $data['pharmacist_id'],
                            'suggested_medicines' => $data['suggested_medicines'],
                            'approved_medicines' => $data['approved_medicines'],
                            'status' => $data['status'],
                        ]);

                        Notification::make()
                            ->title('Pharmacy request sent successfully')
                            ->success()
                            ->send();
                    }),

                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
