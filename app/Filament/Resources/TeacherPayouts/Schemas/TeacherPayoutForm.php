<?php

namespace App\Filament\Resources\TeacherPayouts\Schemas;

use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TeacherPayoutForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Payout details'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('teacher_id')
                                    ->label(__('Teacher'))
                                    ->relationship('teacher', 'name')
                                    ->searchable()
                                    ->required(),
                                TextInput::make('month')
                                    ->label(__('Month (YYYY-MM)'))
                                    ->placeholder('2024-01')
                                    ->required(),
                            ]),
                    ]),

                Section::make(__('Payment details'))
                    ->schema([
                        TextInput::make('total_pay')
                            ->label(__('Total pay'))
                            ->numeric()
                            ->prefix('NT$') // ->prefix('$')
                            ->default(0) // ->default(0.0)
                            ->required(),
                    ]),

                Section::make(__('Payment status'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Checkbox::make('is_paid')
                                    ->label(__('Marked as paid')),
                                DateTimePicker::make('paid_at')
                                    ->label(__('Paid at'))
                                    ->visible(fn ($get) => $get('is_paid')),
                            ]),
                    ]),
            ]);
    }
}
