<?php

namespace App\Filament\Resources\TeacherPayouts\Pages;

use App\Filament\Resources\TeacherPayouts\TeacherPayoutResource;
use App\Services\PayoutCalculationService;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\Alignment;

class ListTeacherPayouts extends ListRecords
{
    protected static string $resource = TeacherPayoutResource::class;

    protected function getHeaderActions(): array
    {
        if (auth()->user()?->isTeacher()) {
            return [];
        }

        return [
            Action::make('generatePayouts')
                ->label(__('Generate monthly payouts'))
                ->icon('heroicon-o-currency-dollar')
                ->color('success')
                ->modalHeading(__('Generate monthly payouts'))
                ->modalDescription(__('Select a month to generate payouts for all teachers based on attendance records.'))
                ->modalWidth('4xl')
                ->schema([
                    Select::make('month')
                        ->label(__('Select month'))
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
                        ->default(now()->format('Y-m'))
                        ->required()
                        ->reactive()
                        ->afterStateUpdated(function ($state, $set) {
                            if ($state) {
                                $service = app(PayoutCalculationService::class);
                                $summary = $service->getMonthlyPayoutSummary($state);
                                $set('summary', $summary);
                            }
                        }),
                ])
                ->modalFooterActionsAlignment(Alignment::Between)
                ->action(function (array $data) {
                    $service = app(PayoutCalculationService::class);
                    $results = $service->generatePayoutsForMonth($data['month']);

                    if (! empty($results['errors'])) {
                        Notification::make()
                            ->title(__('Payout generation completed with errors'))
                            ->body(__('Generated: :generated, Updated: :updated, Skipped: :skipped, Errors: :errors_count', ['generated' => $results['generated'], 'updated' => $results['updated'], 'skipped' => $results['skipped'], 'errors_count' => count($results['errors'])]))
                            ->warning()
                            ->send();
                    } else {
                        Notification::make()
                            ->title(__('Payouts generated successfully!'))
                            ->body(__('Generated: :generated new payouts, Updated: :updated existing payouts, Skipped: :skipped paid payouts'))
                            ->success()
                            ->send();
                    }
                })
                ->modalSubmitActionLabel(__('Generate payouts'))
                ->modalCancelActionLabel(__('Cancel')),
            CreateAction::make(),
        ];
    }
}
