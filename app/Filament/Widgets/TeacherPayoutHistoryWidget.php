<?php

namespace App\Filament\Widgets;

use App\Models\TeacherPayout;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class TeacherPayoutHistoryWidget extends TableWidget
{
    protected static ?int $sort = 3;
    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return Auth::user()?->role?->name === 'Teacher';
    }

    protected function getTableHeading(): ?string
    {
        return __('My payout history');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                TeacherPayout::query()
                    ->where('teacher_id', Auth::id())
                    ->orderBy('created_at', 'desc')
                    ->limit(8)
            )
            ->columns([
                TextColumn::make('month')
                    ->label(__('Month'))
                    ->afterStateHydrated(function (TextColumn $component, string $state) {
                        $state = Carbon::createFromFormat('Y-m', $state);
                        $state->settings(['formatFunction' => 'translatedFormat']);
                        $component->state($state->locale(config('app.locale'))->format('F Y'));
                    })
                    ->sortable(),
                TextColumn::make('total_pay')
                    ->label(__('Amount'))
                    ->money('TWD', decimalPlaces: 0) // ->money('USD')
                    ->color('success'),
                IconColumn::make('is_paid')
                    ->label(__('Status'))
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-clock')
                    ->trueColor('success')
                    ->falseColor('warning'),
                TextColumn::make('paid_at')
                    ->label(__('Paid date'))
                    ->dateTime()
                    ->placeholder(__('Pending'))
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label(__('Generated'))
                    ->date()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated(false);
    }
}
