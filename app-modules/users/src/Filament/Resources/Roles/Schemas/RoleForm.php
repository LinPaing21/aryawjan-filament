<?php

namespace Stella\Users\Filament\Resources\Roles\Schemas;

use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Spatie\Permission\Models\Permission;

class RoleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),

                CheckboxList::make('permissions')
                    ->relationship(titleAttribute: 'name')
                    ->columns(['default' => 1, 'sm' => 2, 'lg' => 3, 'xl' => 4])
                    ->searchable()
                    ->bulkToggleable()
                    ->columnSpanFull()
            ]);
    }
}
