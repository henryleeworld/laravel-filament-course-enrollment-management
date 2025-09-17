<?php

namespace App\Filament\Resources\Courses\Schemas;

use App\Models\ClassType;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CourseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('class_type_id')
                    ->label(__('Class type'))
                    ->relationship('classType', 'name')
                    ->getOptionLabelFromRecordUsing(fn (ClassType $record) => __($record->name))
                    ->required(),
                Select::make('teacher_id')
                    ->label(__('Teacher'))
                    ->relationship('teacher', 'name')
                    ->required(),
                TextInput::make('name')
                    ->label(__('Course name'))
                    ->required()
                    ->maxLength(255),
                Textarea::make('description')
                    ->label(__('Course description'))
                    ->columnSpanFull(),
                TextInput::make('price_per_student')
                    ->label(__('Price per student'))
                    ->numeric()
                    ->prefix('NT$') // ->prefix('$')
                    ->step(1), // ->step(0.01)
                CheckboxList::make('students')
                    ->label(__('Students'))
                    ->relationship('students', 'first_name', function ($query) {
                        return $query->orderBy('last_name')->orderBy('first_name');
                    })
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->first_name} {$record->last_name}")
                    ->columns(4)
                    ->columnSpanFull(),
            ]);
    }
}
