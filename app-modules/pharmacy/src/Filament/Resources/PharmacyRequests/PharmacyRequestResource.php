<?php

namespace Stella\Pharmacy\Filament\Resources\PharmacyRequests;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Stella\Pharmacy\Filament\Resources\PharmacyRequests\Pages\ManagePharmacyRequests;
use Stella\Pharmacy\Filament\Resources\PharmacyRequests\Schemas\PharmacyRequestForm;
use Stella\Pharmacy\Filament\Resources\PharmacyRequests\Schemas\PharmacyRequestInfolist;
use Stella\Pharmacy\Filament\Resources\PharmacyRequests\Tables\PharmacyRequestsTable;
use Stella\Pharmacy\Models\PharmacyRequest;
use UnitEnum;

class PharmacyRequestResource extends Resource
{
    protected static ?string $model = PharmacyRequest::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::InboxArrowDown;

    protected static string|UnitEnum|null $navigationGroup = 'Pharmacy';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'id';

    public static function canAccess(): bool
    {
        return auth()->user()->canAny(['manage pharmacy requests', 'view pharmacy requests']);
    }

    public static function form(Schema $schema): Schema
    {
        return PharmacyRequestForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PharmacyRequestInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PharmacyRequestsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            // Only using the index page for a simple resource with modals
            'index' => ManagePharmacyRequests::route('/'),
        ];
    }
}
