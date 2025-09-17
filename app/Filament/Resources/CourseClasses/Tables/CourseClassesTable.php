<?php

namespace App\Filament\Resources\CourseClasses\Tables;

use App\Services\ScheduleGenerationService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Carbon;

class CourseClassesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('course.name')
                    ->label(__('Course'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('scheduled_date')
                    ->label(__('Scheduled date'))
                    ->date()
                    ->sortable(),
                TextColumn::make('time_range')
                    ->label(__('Time range'))
                    ->getStateUsing(fn ($record) => substr($record->start_time, 0, 5).' - '.substr($record->end_time, 0, 5)
                    )
                    ->sortable(query: function ($query, $direction) {
                        return $query->orderBy('start_time', $direction);
                    }),
                TextColumn::make('teacher.name')
                    ->label(__('Teacher'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('substituteTeacher.name')
                    ->label(__('Substitute teacher'))
                    ->sortable()
                    ->searchable()
                    ->placeholder('—'),
                TextColumn::make('attendances_count')
                    ->label(__('Students'))
                    ->alignCenter()
                    ->counts('attendances')
                    ->sortable(),
                TextColumn::make('teacher_total_pay')
                    ->label(__('Teacher total pay'))
                    ->money('TWD', decimalPlaces: 0) // ->money('USD')
                    ->sortable(),
                TextColumn::make('substitute_total_pay')
                    ->label(__('Substitute teacher total pay'))
                    ->money('TWD', decimalPlaces: 0) // ->money('USD')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('Created at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('Updated at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('course_id')
                    ->label(__('Course'))
                    ->relationship('course', 'name'),
                SelectFilter::make('teacher_id')
                    ->label(__('Teacher'))
                    ->relationship('teacher', 'name'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->headerActions(
                auth()->user()?->isAdmin()
                    ? [
                        Action::make('generate_monthly_schedules')
                            ->label(__('Generate monthly schedules'))
                            ->color('success')
                            ->icon('heroicon-o-calendar-days')
                            ->modalHeading(__('Generate class schedules for month'))
                            ->modalDescription(__('Generate class schedules based on weekly schedule patterns. Note: Schedules can only be generated once per month.'))
                            ->schema([
                                Select::make('month_year')
                                    ->label(_('Select month'))
                                    ->options(function () {
                                        $service = new ScheduleGenerationService;

                                        return $service->getAvailableMonthsForGeneration()
                                            ->pluck('label', 'value')
                                            ->toArray();
                                    })
                                    ->placeholder(__('Choose a month...'))
                                    ->required(),
                            ])
                            ->action(function (array $data) {
                                try {
                                    [$year, $month] = explode('-', $data['month_year']);
                                    $service = new ScheduleGenerationService;

                                    $createdSchedules = $service->generateMonthlySchedules((int) $year, (int) $month);

                                    $monthName = Carbon::create($year, $month, 1)->format('F Y');

                                    Notification::make()
                                        ->title(__('Schedules generated successfully!'))
                                        ->body(sprintf(__('Created %d class schedules for %s.'), count($createdSchedules), $monthName))
                                        ->success()
                                        ->send();

                                } catch (\Exception $e) {
                                    Notification::make()
                                        ->title(__('Generation failed'))
                                        ->body($e->getMessage())
                                        ->danger()
                                        ->send();
                                }
                            }),
                    ]
                    : []
            )
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('scheduled_date', 'asc');
    }
}
