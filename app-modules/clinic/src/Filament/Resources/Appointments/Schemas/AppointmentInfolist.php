<?php

namespace Stella\Clinic\Filament\Resources\Appointments\Schemas;

use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Stella\Ai\Enums\Severity;
use Stella\Clinic\Enums\AppointmentStatus;
use Storage;

class AppointmentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('token_number')
                    ->badge()
                    ->columnSpanFull(),
                TextEntry::make('patient.name')
                    ->label('Patient'),
                TextEntry::make('doctor.name')
                    ->label('Doctor')
                    ->placeholder('Unassigned'),
                // RepeatableEntry::make('triageLog.attachments')
                // ->schema([
                ImageEntry::make('triageLog.attachments')
                    ->label('Attachments')
                    ->url(
                        fn ($state) => $state ? Storage::temporaryUrl($state, now()->addMinutes(5)) : null,
                        shouldOpenInNewTab: true
                    )
                    ->openUrlInNewTab()
                    ->defaultImageUrl(url('images/elementor-placeholder-image.png')),
                // ])
                // ->placeholder('No Attachments Available.')
                // ->columnSpanFull(),
                TextEntry::make('triageLog.raw_symptoms')
                    ->label('Symptoms')
                    ->columnSpanFull()
                    ->placeholder('Patient Symptoms Not Found.'),
                TextEntry::make('triageLog.ai_analysis.reasoning')
                    ->label('AI Analysis')
                    ->columnSpanFull()
                    ->placeholder('No AI Analysis Available.'),
                TextEntry::make('triageLog.severity')
                    ->label('AI Severity Assessment')
                    ->badge()
                    ->colors([
                        'danger' => Severity::HIGH,
                        'warning' => Severity::MEDIUM,
                        'success' => Severity::LOW,
                    ])
                    ->placeholder('No AI Severity Assessment Available.'),
                TextEntry::make('status')
                    ->badge()
                    ->formatStateUsing(fn (AppointmentStatus $state): string => $state->label())
                    ->color(fn (AppointmentStatus $state): string => $state->color()),
                TextEntry::make('scheduled_at')
                    ->date()
                    ->placeholder('Not Scheduled'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
