<?php

namespace App\Filament\Widgets;

use App\Models\Maintenance;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class MaintenanceStatusWidget extends BaseWidget
{
    protected static bool $isLazy = true;

    protected static ?int $sort = 4;

    protected function getStats(): array
    {
        $pending = Maintenance::where('status', 'معلقة')->count();
        $inProgress = Maintenance::where('status', 'قيد التنفيذ')->count();
        $completed = Maintenance::where('status', 'مكتملة')->count();
        $urgent = Maintenance::where('priority', 'عاجلة')
            ->whereIn('status', ['معلقة', 'قيد التنفيذ'])
            ->count();

        return [
            Stat::make('طلبات معلقة', $pending)
                ->description('طلبات صيانة في الانتظار')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning')
                ->url(route('filament.admin.resources.maintenances.index', ['tableFilters' => ['status' => ['value' => 'معلقة']]])),
            Stat::make('قيد التنفيذ', $inProgress)
                ->description('طلبات جاري تنفيذها')
                ->descriptionIcon('heroicon-m-wrench-screwdriver')
                ->color('info')
                ->url(route('filament.admin.resources.maintenances.index', ['tableFilters' => ['status' => ['value' => 'قيد التنفيذ']]])),
            Stat::make('مكتملة', $completed)
                ->description('طلبات تم إنجازها')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
            Stat::make('عاجلة', $urgent)
                ->description('طلبات تحتاج معالجة فورية')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('danger')
                ->url(route('filament.admin.resources.maintenances.index', ['tableFilters' => ['priority' => ['value' => 'عاجلة']]])),
        ];
    }
}
