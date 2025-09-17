<?php

namespace App\Filament\Resources\Courses\RelationManagers;

use App\Enums\DayOfWeek;
use App\Filament\Schemas\Components\TimeSelect;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\Select;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class WeeklySchedulesRelationManager extends RelationManager
{
    protected static string $relationship = 'weeklySchedules';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Schedule details'))
                    ->schema([
                        Select::make('course_id')
                            ->label(__('Course'))
                            ->default(fn (self $livewire) => $livewire->getOwnerRecord()->getKey())
                            ->disabled()
                            ->dehydrated()
                            ->relationship('course', 'name')
                            ->required(),
                        Grid::make(3)
                            ->schema([
                                Select::make('day_of_week')
                                    ->label(__('Day of week'))
                                    ->options(DayOfWeek::options())
                                    ->required(),
                                TimeSelect::make('start_time')
                                    ->label(__('Start time'))
                                    ->required(),
                                TimeSelect::make('end_time')
                                    ->label(__('End time'))
                                    ->required(),
                            ]),
                    ]),
            ]);
    }

    protected function getTableHeading(): string
    {
        return __('Weekly schedules');
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('day_name')
                    ->label(__('Day'))
                    ->sortable(),
                TextColumn::make('start_time')
                    ->label(__('Start time'))
                    ->time('H:i')
                    ->sortable(),
                TextColumn::make('end_time')
                    ->label(__('End time'))
                    ->time('H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('day_of_week')
                    ->label(__('Day of week'))
                    ->options(DayOfWeek::options()),
            ])
            ->headerActions([
                Action::make('create')
                    ->label(__('Create'))
                    ->modalHeading(__('Create weekly schedule'))
                    ->schema($this->form(new Schema)->getComponents())
                    ->action(function (array $data) {
                        $this->getOwnerRecord()->weeklySchedules()->create($data);
                    }),
            ])
            ->recordActions([
                Action::make('edit')
                    ->label(__('Edit'))
                    ->modalHeading(__('Edit weekly schedule'))
                    ->fillForm(fn ($record) => $record->toArray())
                    ->schema($this->form(new Schema)->getComponents())
                    ->action(function (array $data, $record) {
                        $record->update($data);
                    }),
                Action::make('delete')
                    ->label(__('Delete'))
                    ->requiresConfirmation()
                    ->action(fn ($record) => $record->delete()),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
