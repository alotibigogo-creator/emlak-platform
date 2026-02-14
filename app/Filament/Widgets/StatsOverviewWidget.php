<?php

namespace App\Filament\Widgets;

use App\Models\Property;
use App\Models\Unit;
use App\Models\Contract;
use App\Models\Customer;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected static bool $isLazy = true;

    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $totalProperties = Property::count();
        $totalUnits = Unit::count();
        $activeContracts = Contract::where('status', 'نشط')->count();
        $totalCustomers = Customer::count();
        $occupiedUnits = Unit::where('status', 'مؤجرة')->count();
        $occupancyRate = $totalUnits > 0 ? round(($occupiedUnits / $totalUnits) * 100, 1) : 0;

        return [
            Stat::make('إجمالي العقارات', $totalProperties)
                ->description('عدد العقارات المسجلة')
                ->descriptionIcon('heroicon-m-building-office-2')
                ->color('success')
                ->chart([7, 3, 4, 5, 6, 3, 5, 3]),
            Stat::make('إجمالي الوحدات', $totalUnits)
                ->description('عدد الوحدات المتاحة')
                ->descriptionIcon('heroicon-m-building-storefront')
                ->color('info')
                ->chart([3, 4, 5, 6, 5, 6, 7, 8]),
            Stat::make('العقود النشطة', $activeContracts)
                ->description('عقود ساري مفعولها')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('warning')
                ->chart([2, 3, 4, 5, 6, 5, 4, 5]),
            Stat::make('نسبة الإشغال', $occupancyRate . '%')
                ->description($occupiedUnits . ' من ' . $totalUnits . ' وحدة مؤجرة')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color($occupancyRate > 70 ? 'success' : ($occupancyRate > 50 ? 'warning' : 'danger'))
                ->chart([65, 70, 68, 72, 75, 78, 80, $occupancyRate]),
        ];
    }
}
