<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\Role;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('User information'))
                    ->schema([
                        TextInput::make('name')
                            ->label(__('Name'))
                            ->maxLength(255)
                            ->required(),
                        TextInput::make('email')
                            ->label(__('Email'))
                            ->email()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->required(),
                        Select::make('role_id')
                            ->label(__('Role'))
                            ->relationship('role', 'name')
                            ->getOptionLabelFromRecordUsing(fn (Role $record) => __($record->name))
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('password')
                            ->label(__('Password'))
                            ->password()
                            ->visible(fn (string $context): bool => $context === 'create')
                            ->minLength(8)
                            ->dehydrated(fn (?string $state): bool => filled($state)),                            
                    ]),
            ]);
    }
}
