<?php

namespace Stella\Ai\Filament\Resources\MedicalKnowledge;

use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Stella\Ai\Filament\Resources\MedicalKnowledge\Pages\ManageMedicalKnowledge;
use Stella\Ai\Models\MedicalKnowledge;
use UnitEnum;

class MedicalKnowledgeResource extends Resource
{
    protected static ?string $model = MedicalKnowledge::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::BookOpen;

    protected static ?string $recordTitleAttribute = 'Medical Knowledge';

    protected static string|UnitEnum|null $navigationGroup = 'Clinic';

    public static function canAccess(): bool
    {
        return auth()->user()->can('manage medical knowledge');
    }

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                FileUpload::make('medical_knowledge')
                    ->storeFileNamesIn('original_filename')
                    ->acceptedFileTypes(['application/pdf'])
                    ->columnSpanFull(),
                Textarea::make('remark')->rows(3)->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('Medical Knowledge')
            ->columns([
                TextColumn::make('content')->searchable(),
                TextColumn::make('metadata.filename')
                    ->label('File Name')
                    ->default('-')
                    ->searchable(),
                TextColumn::make('metadata.filepath')->label('File Path')->default('-'),
                TextColumn::make('metadata.created_by')
                    ->label('Created By')
                    ->searchable()
                    ->default('-')
                    ->formatStateUsing(fn(int $state) => \App\Models\User::find($state)?->name ?? '-')
            ])
            ->filters([
                //
            ])
            ->recordActions([
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
            'index' => ManageMedicalKnowledge::route('/'),
        ];
    }
}

