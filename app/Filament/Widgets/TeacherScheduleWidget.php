<?php

namespace App\Filament\Widgets;

use App\Models\CourseClass;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Support\Facades\Auth;

class TeacherScheduleWidget extends TableWidget
{
    protected static ?int $sort = 1;
    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return Auth::user()?->role?->name === 'Teacher';
    }

    protected function getTableHeading(): ?string
    {
        return __('My upcoming classes');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                CourseClass::query()
                    ->where('teacher_id', Auth::id())
                    ->with(['course', 'substituteTeacher'])
                    ->where('scheduled_date', '>=', now()->toDateString())
                    ->orderBy('scheduled_date')
                    ->orderBy('start_time')
                    ->limit(10)
            )
            ->columns([
                TextColumn::make('scheduled_date')
                    ->label(__('Scheduled date'))
                    ->date()
                    ->sortable(),
                TextColumn::make('start_time')
                    ->label(__('Start time'))
                    ->time('H:i')
                    ->badge()
                    ->color('primary'),
                TextColumn::make('course.name')
                    ->label(__('Course'))
                    ->searchable(),
                IconColumn::make('substitute_teacher_id')
                    ->label(__('Substitute teacher'))
                    ->boolean()
                    ->trueIcon('heroicon-o-user')
                    ->falseIcon('heroicon-o-minus')
                    ->tooltip(fn ($record) => $record->substitute_teacher_id
                        ? __('Substitute teacher: ') . $record->substituteTeacher?->name
                        : null
                    ),
            ])
            ->defaultSort('scheduled_date')
            ->paginated(false);
    }
}
