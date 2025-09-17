<?php

namespace App\Filament\Resources\TeacherPayouts\Tables;

use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class TeacherPayoutsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('teacher.name')
                    ->label(__('Teacher'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('month')
                    ->label(__('Month'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('total_pay')
                    ->label(__('Total pay'))
                    ->money('TWD', decimalPlaces: 0) // ->money('USD')
                    ->sortable()
                    ->weight('bold'),
                IconColumn::make('is_paid')
                    ->label(__('Paid'))
                    ->boolean()
                    ->alignCenter(),
                TextColumn::make('paid_at')
                    ->label(__('Paid at'))
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
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
                SelectFilter::make('month')
                    ->label(__('Month'))
                    ->options(function () {
                        $months = [];
                        $current = now()->startOfMonth();
                        for ($i = -6; $i <= 6; $i++) {
                            $date = $current->copy()->addMonths($i);
                            $date->settings(['formatFunction' => 'translatedFormat']);
                            $months[$date->format('Y-m')] = $date->locale(config('app.locale'))->format('F Y');
                        }

                        return $months;
                    })
                    ->default(now()->format('Y-m')),
                Filter::make('is_paid')
                    ->label(__('Paid'))
                    ->query(fn (Builder $query): Builder => $query->where('is_paid', true)),
                Filter::make('unpaid')
                    ->label(__('Unpaid'))
                    ->query(fn (Builder $query): Builder => $query->where('is_paid', false)),
            ])
            ->recordActions(
                auth()->user()?->isAdmin()
                    ? [EditAction::make()]
                    : []
            )
            ->toolbarActions(
                auth()->user()?->isAdmin()
                    ? [
                        BulkActionGroup::make([
                            BulkAction::make('markAsPaid')
                                ->label(__('Mark as paid'))
                                ->icon('heroicon-o-check-circle')
                                ->color('success')
                                ->requiresConfirmation()
                                ->action(function (Collection $records): void {
                                    $now = now();

                                    $records->each(function ($record) use ($now) {
                                        if (! $record->is_paid) {
                                            $record->update([
                                                'is_paid' => true,
                                                'paid_at' => $now,
                                            ]);
                                        }
                                    });
                                })
                                ->deselectRecordsAfterCompletion(),
                            DeleteBulkAction::make(),
                        ]),
                    ]
                    : []
            );
    }
}
