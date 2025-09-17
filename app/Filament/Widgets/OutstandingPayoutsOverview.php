<?php

namespace App\Filament\Widgets;

use App\Models\TeacherPayout;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class OutstandingPayoutsOverview extends StatsOverviewWidget
{
    public static function canView(): bool
    {
        return in_array(Auth::user()?->role?->name, ['Admin', 'Owner']);
    }

    protected function getStats(): array
    {
        $unpaidPayouts = TeacherPayout::where('is_paid', false)->get();
        $totalUnpaid = $unpaidPayouts->sum('total_pay');
        $unpaidCount = $unpaidPayouts->count();

        $thisMonthPaid = TeacherPayout::where('is_paid', true)
            ->whereMonth('paid_at', now()->month)
            ->whereYear('paid_at', now()->year)
            ->sum('total_pay');

        return [
            Stat::make(__('Outstanding payouts'), 'NT$'.number_format($totalUnpaid)) // Stat::make(__('Outstanding payouts'), '$'.number_format($totalUnpaid, 2))
                ->description($unpaidCount . ' ' . __('teachers awaiting payment'))
                ->descriptionIcon('heroicon-o-clock')
                ->color('warning'),
            Stat::make(__('Unpaid teachers'), $unpaidCount)
                ->description(__('Teachers with pending payments'))
                ->descriptionIcon('heroicon-o-user-group')
                ->color($unpaidCount > 0 ? 'danger' : 'success'),
            Stat::make(__('This month paid'), 'NT$'.number_format($thisMonthPaid)) // Stat::make(__('This month paid'), '$'.number_format($thisMonthPaid, 2))
                ->description(__('Total payments made this month'))
                ->descriptionIcon('heroicon-o-check-circle')
                ->color('success'),
        ];
    }
}
