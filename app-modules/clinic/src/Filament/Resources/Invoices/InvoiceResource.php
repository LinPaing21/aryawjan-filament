<?php

namespace Stella\Clinic\Filament\Resources\Invoices;

use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Stella\Clinic\Filament\Resources\Invoices\Pages\ManageInvoices;
use Stella\Clinic\Models\Invoice;
use UnitEnum;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::CurrencyDollar;

    protected static string|UnitEnum|null $navigationGroup = 'Clinic';

    protected static ?int $navigationSort = 2;

    public static function canAccess(): bool
    {
        return auth()->user()->canAny(['manage invoices', 'view invoices']);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('medical_record_id')
                    ->relationship('medicalRecord', 'id')
                    ->required(),
                Select::make('patient_id')
                    ->relationship('patient', 'name')
                    ->required(),
                TextInput::make('consultation_fee')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('medicine_fee')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('total_amount')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('status')
                    ->required()
                    ->default('unpaid'),
                TextInput::make('payment_method'),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('medicalRecord.id')
                    ->label('Medical record'),
                TextEntry::make('patient.name')
                    ->label('Patient'),
                TextEntry::make('consultation_fee')
                    ->numeric(),
                TextEntry::make('medicine_fee')
                    ->numeric(),
                TextEntry::make('total_amount')
                    ->numeric(),
                TextEntry::make('status'),
                TextEntry::make('payment_method')
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('medicalRecord.id')
                    ->searchable(),
                TextColumn::make('patient.name')
                    ->searchable(),
                TextColumn::make('consultation_fee')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('medicine_fee')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('total_amount')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('status')
                    ->searchable(),
                TextColumn::make('payment_method')
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
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageInvoices::route('/'),
        ];
    }
}
