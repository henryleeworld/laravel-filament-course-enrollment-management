<?php

namespace App\Filament\Resources\Students\Schemas;

use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class StudentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('first_name')
                    ->label(__('First name'))
                    ->maxLength(100)
                    ->required(),
                TextInput::make('last_name')
                    ->label(__('Last name'))
                    ->maxLength(100)
                    ->required(),
                TextInput::make('email')
                    ->label(__('Email'))
                    ->email()
                    ->maxLength(255)
                    ->required(),
                CheckboxList::make('courses')
                    ->label(__('Courses'))
                    ->relationship('courses', 'name')
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }
}
