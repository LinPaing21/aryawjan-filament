<?php

namespace Stella\Clinic\Filament\Resources\Appointments\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Stella\Clinic\Enums\AppointmentStatus;
use Stella\Clinic\Models\Appointment;

class AppointmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('token_number')
                    ->searchable()
                    ->sortable()
                    ->badge(),
                TextColumn::make('patient.name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('doctor.name')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Unassigned'),
                TextColumn::make('scheduled_at')
                    ->date()
                    ->sortable()
                    ->placeholder('Unscheduled'),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (AppointmentStatus $state): string => $state->label())
                    ->color(fn (AppointmentStatus $state): string => $state->color()),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(
                        collect(AppointmentStatus::cases())
                            ->mapWithKeys(fn (AppointmentStatus $status) => [$status->value => $status->label()])
                    ),
            ])
            ->defaultSort(function (Builder $query): Builder {
                return $query->orderBy('scheduled_at', 'desc')->orderBy('token_number', 'desc');
            })
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('mark_missed')
                    ->label('Mark Missed')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Mark as Missed')
                    ->modalDescription('The patient did not appear. You can re-queue them later when they return.')
                    ->visible(fn (Appointment $record): bool => $record->status === AppointmentStatus::Booked)
                    ->action(fn (Appointment $record) => $record->update(['status' => AppointmentStatus::Missed])),
                Action::make('re_queue')
                    ->label('Re-queue')
                    ->icon('heroicon-o-arrow-path')
                    ->color('info')
                    ->requiresConfirmation()
                    ->modalHeading('Re-queue Patient')
                    ->modalDescription('The patient has returned. They will be served next ahead of the regular queue.')
                    ->visible(fn (Appointment $record): bool => $record->status === AppointmentStatus::Missed)
                    ->action(fn (Appointment $record) => $record->update(['status' => AppointmentStatus::ReQueued])),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
