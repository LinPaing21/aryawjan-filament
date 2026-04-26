<?php

namespace Stella\Users\Filament\Resources\Roles;

use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Spatie\Permission\Models\Role;
use Stella\Users\Filament\Resources\Roles\Pages\CreateRole;
use Stella\Users\Filament\Resources\Roles\Pages\EditRole;
use Stella\Users\Filament\Resources\Roles\Pages\ListRoles;
use Stella\Users\Filament\Resources\Roles\Schemas\RoleForm;
use Stella\Users\Filament\Resources\Roles\Tables\RolesTable;

class RoleResource extends Resource
{
    protected static ?string $model = Role::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::UserGroup;

    protected static string | UnitEnum | null $navigationGroup = 'Access';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'Role Management';

    public static function canAccess(): bool
    {
        return auth()->user()->can('manage roles');
    }

    public static function form(Schema $schema): Schema
    {
        return RoleForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RolesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRoles::route('/'),
            'create' => CreateRole::route('/create'),
            'edit' => EditRole::route('/{record}/edit'),
        ];
    }
}
