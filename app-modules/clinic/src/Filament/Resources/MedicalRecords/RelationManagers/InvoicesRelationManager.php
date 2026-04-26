<?php

namespace Stella\Clinic\Filament\Resources\MedicalRecords\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Stella\Clinic\Models\Invoice;

class InvoicesRelationManager extends RelationManager
{
    protected static string $relationship = 'invoice';

    protected static ?string $recordTitleAttribute = 'id';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('consultation_fee')
                    ->numeric()
                    ->default(0)
                    ->required()
                    ->live(),
                TextInput::make('medicine_fee')
                    ->numeric()
                    ->default(0)
                    ->required()
                    ->live(),
                TextInput::make('total_amount')
                    ->numeric()
                    ->readOnly()
                    ->state(fn ($get) => (float) $get('consultation_fee') + (float) $get('medicine_fee')),
                Select::make('status')
                    ->options([
                        'unpaid' => 'Unpaid',
                        'paid' => 'Paid',
                    ])
                    ->default('unpaid')
                    ->required(),
                TextInput::make('payment_method'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->sortable(),
                TextColumn::make('consultation_fee')->money('MMK')->sortable(),
                TextColumn::make('medicine_fee')->money('MMK')->sortable(),
                TextColumn::make('total_amount')->money('MMK')->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'paid' => 'success',
                        'unpaid' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['patient_id'] = $this->getOwnerRecord()->patient_id;
                        return $data;
                    }),
            ]);
    }
}
