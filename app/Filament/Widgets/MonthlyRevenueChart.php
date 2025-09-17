<?php

namespace App\Filament\Widgets;

use App\Models\CourseClass;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MonthlyRevenueChart extends ChartWidget
{
    protected ?string $heading = 'Monthly revenue';

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return in_array(Auth::user()?->role?->name, ['Admin', 'Owner']);
    }

    protected function getData(): array
    {
        $monthlyRevenue = CourseClass::query()
            ->select(
                DB::raw('DATE_FORMAT(scheduled_date, "%Y-%m") as month'),
                DB::raw('SUM(student_count * (SELECT price_per_student FROM courses WHERE id = course_classes.course_id)) as revenue')
            )
            ->where('scheduled_date', '>=', now()->subMonths(11))
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->pluck('revenue', 'month');

        $labels = [];
        $data = [];

        for ($i = 11; $i >= 0; $i--) {
            $subMonth = now()->subMonths($i);
            $month = $subMonth->format('Y-m');
            $subMonth->settings(['formatFunction' => 'translatedFormat']);
            $monthName = $subMonth->locale(config('app.locale'))->format('M Y');
            $labels[] = $monthName;
            $data[] = $monthlyRevenue->get($month, 0);
        }

        return [
            'datasets' => [
                [
                    'label' => __('Revenue'),
                    'data' => $data,
                    'borderColor' => 'rgb(59, 130, 246)',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'fill' => true,
                ],
            ],
            'labels' => $labels,
        ];
    }

    public function getHeading(): string
    {
        return __($this->heading);
    }

    public function getHeight(): ?string
    {
        return '500px';
    }

    protected function getOptions(): array
    {
        return [
            'maintainAspectRatio' => false,
            'responsive' => true,
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
