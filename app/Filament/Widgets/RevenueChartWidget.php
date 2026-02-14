<?php

namespace App\Filament\Widgets;

use App\Models\Revenue;
use App\Models\Expense;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class RevenueChartWidget extends ChartWidget
{
    protected static bool $isLazy = true;

    protected static ?string $heading = 'الإيرادات والمصروفات الشهرية';

    protected static ?int $sort = 2;

    protected static ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $currentYear = Carbon::now()->year;

        $revenues = Revenue::whereYear('date', $currentYear)
            ->selectRaw("CAST(strftime('%m', date) AS INTEGER) as month, SUM(amount) as total")
            ->where('status', 'مدفوعة')
            ->groupBy('month')
            ->pluck('total', 'month')
            ->toArray();

        $expenses = Expense::whereYear('date', $currentYear)
            ->selectRaw("CAST(strftime('%m', date) AS INTEGER) as month, SUM(amount) as total")
            ->groupBy('month')
            ->pluck('total', 'month')
            ->toArray();

        $months = [
            'يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو',
            'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر'
        ];

        $revenueData = [];
        $expenseData = [];

        for ($i = 1; $i <= 12; $i++) {
            $revenueData[] = $revenues[$i] ?? 0;
            $expenseData[] = $expenses[$i] ?? 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'الإيرادات',
                    'data' => $revenueData,
                    'backgroundColor' => 'rgba(34, 197, 94, 0.2)',
                    'borderColor' => 'rgb(34, 197, 94)',
                    'borderWidth' => 2,
                ],
                [
                    'label' => 'المصروفات',
                    'data' => $expenseData,
                    'backgroundColor' => 'rgba(239, 68, 68, 0.2)',
                    'borderColor' => 'rgb(239, 68, 68)',
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $months,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
