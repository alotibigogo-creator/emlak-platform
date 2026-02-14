<?php

namespace App\Filament\Resources\PropertyResource\Pages;

use App\Filament\Resources\PropertyResource;
use App\Filament\Resources\PropertyResource\RelationManagers;
use Filament\Actions;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\FontWeight;

class ViewProperty extends ViewRecord
{
    protected static string $resource = PropertyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()->label('تعديل'),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Tabs::make('التبويبات')
                    ->tabs([
                        Infolists\Components\Tabs\Tab::make('بيانات العقار')
                            ->icon('heroicon-o-building-office-2')
                            ->schema([
                                Infolists\Components\Grid::make(3)
                                    ->schema([
                                        Infolists\Components\TextEntry::make('code')
                                            ->label('رمز العقار')
                                            ->badge()
                                            ->color('success'),
                                        Infolists\Components\TextEntry::make('name')
                                            ->label('اسم العقار')
                                            ->weight(FontWeight::Bold),
                                        Infolists\Components\TextEntry::make('type')
                                            ->label('النوع')
                                            ->badge()
                                            ->color(fn (string $state): string => match ($state) {
                                                'سكني' => 'success',
                                                'تجاري' => 'warning',
                                                'إداري' => 'info',
                                                'أرض' => 'gray',
                                                default => 'gray',
                                            }),
                                    ]),
                                Infolists\Components\Grid::make(2)
                                    ->schema([
                                        Infolists\Components\TextEntry::make('address')
                                            ->label('العنوان')
                                            ->icon('heroicon-m-map-pin'),
                                        Infolists\Components\TextEntry::make('area')
                                            ->label('المساحة')
                                            ->suffix(' متر مربع')
                                            ->icon('heroicon-m-squares-2x2'),
                                    ]),
                                Infolists\Components\TextEntry::make('description')
                                    ->label('الوصف')
                                    ->columnSpanFull(),
                                Infolists\Components\Section::make('الإحصائيات')
                                    ->schema([
                                        Infolists\Components\Grid::make(4)
                                            ->schema([
                                                Infolists\Components\TextEntry::make('units_count')
                                                    ->label('عدد الوحدات')
                                                    ->state(fn ($record) => $record->units()->count())
                                                    ->badge()
                                                    ->color('info'),
                                                Infolists\Components\TextEntry::make('occupied_units')
                                                    ->label('الوحدات المؤجرة')
                                                    ->state(fn ($record) => $record->units()->where('status', 'مؤجرة')->count())
                                                    ->badge()
                                                    ->color('success'),
                                                Infolists\Components\TextEntry::make('active_contracts')
                                                    ->label('العقود النشطة')
                                                    ->state(fn ($record) => $record->contracts()->where('contracts.status', 'نشط')->count())
                                                    ->badge()
                                                    ->color('warning'),
                                                Infolists\Components\TextEntry::make('total_revenue')
                                                    ->label('إجمالي الإيرادات')
                                                    ->state(fn ($record) => number_format($record->revenues()->where('status', 'مدفوعة')->sum('amount'), 2) . ' ر.س')
                                                    ->badge()
                                                    ->color('success'),
                                            ]),
                                    ]),
                            ]),

                        Infolists\Components\Tabs\Tab::make('المالك')
                            ->icon('heroicon-o-user-circle')
                            ->schema([
                                Infolists\Components\Grid::make(2)
                                    ->schema([
                                        Infolists\Components\TextEntry::make('owner.name')
                                            ->label('اسم المالك')
                                            ->icon('heroicon-m-user')
                                            ->weight(FontWeight::Bold)
                                            ->default('غير محدد'),
                                        Infolists\Components\TextEntry::make('owner.phone')
                                            ->label('رقم الجوال')
                                            ->icon('heroicon-m-phone')
                                            ->default('غير محدد'),
                                        Infolists\Components\TextEntry::make('owner.email')
                                            ->label('البريد الإلكتروني')
                                            ->icon('heroicon-m-envelope')
                                            ->default('غير محدد'),
                                        Infolists\Components\TextEntry::make('owner.identity_number')
                                            ->label('رقم الهوية')
                                            ->icon('heroicon-m-identification')
                                            ->default('غير محدد'),
                                    ]),
                                Infolists\Components\TextEntry::make('owner.address')
                                    ->label('عنوان المالك')
                                    ->icon('heroicon-m-map-pin')
                                    ->default('غير محدد')
                                    ->columnSpanFull(),
                                Infolists\Components\TextEntry::make('owner.notes')
                                    ->label('ملاحظات')
                                    ->default('لا توجد ملاحظات')
                                    ->columnSpanFull(),
                            ]),

                        Infolists\Components\Tabs\Tab::make('التقارير')
                            ->icon('heroicon-o-document-chart-bar')
                            ->schema([
                                Infolists\Components\Section::make('الملخص المالي')
                                    ->schema([
                                        Infolists\Components\Grid::make(3)
                                            ->schema([
                                                Infolists\Components\TextEntry::make('report_total_revenue')
                                                    ->label('إجمالي الإيرادات')
                                                    ->state(fn ($record) => number_format($record->revenues()->where('status', 'مدفوعة')->sum('amount'), 2) . ' ر.س')
                                                    ->badge()
                                                    ->color('success'),
                                                Infolists\Components\TextEntry::make('report_total_expenses')
                                                    ->label('إجمالي المصروفات')
                                                    ->state(fn ($record) => number_format($record->expenses()->sum('amount'), 2) . ' ر.س')
                                                    ->badge()
                                                    ->color('danger'),
                                                Infolists\Components\TextEntry::make('report_net_income')
                                                    ->label('صافي الدخل')
                                                    ->state(function ($record) {
                                                        $revenue = $record->revenues()->where('status', 'مدفوعة')->sum('amount');
                                                        $expense = $record->expenses()->sum('amount');
                                                        $net = $revenue - $expense;
                                                        return number_format($net, 2) . ' ر.س';
                                                    })
                                                    ->badge()
                                                    ->color(function ($record) {
                                                        $revenue = $record->revenues()->where('status', 'مدفوعة')->sum('amount');
                                                        $expense = $record->expenses()->sum('amount');
                                                        return ($revenue - $expense) >= 0 ? 'success' : 'danger';
                                                    }),
                                            ]),
                                    ]),
                                Infolists\Components\Section::make('إحصائيات الوحدات')
                                    ->schema([
                                        Infolists\Components\Grid::make(4)
                                            ->schema([
                                                Infolists\Components\TextEntry::make('report_total_units')
                                                    ->label('إجمالي الوحدات')
                                                    ->state(fn ($record) => $record->units()->count())
                                                    ->badge()
                                                    ->color('info'),
                                                Infolists\Components\TextEntry::make('report_available_units')
                                                    ->label('متاحة')
                                                    ->state(fn ($record) => $record->units()->where('status', 'متاحة')->count())
                                                    ->badge()
                                                    ->color('success'),
                                                Infolists\Components\TextEntry::make('report_rented_units')
                                                    ->label('مؤجرة')
                                                    ->state(fn ($record) => $record->units()->where('status', 'مؤجرة')->count())
                                                    ->badge()
                                                    ->color('warning'),
                                                Infolists\Components\TextEntry::make('report_occupancy_rate')
                                                    ->label('نسبة الإشغال')
                                                    ->state(function ($record) {
                                                        $total = $record->units()->count();
                                                        if ($total === 0) return '0%';
                                                        $rented = $record->units()->where('status', 'مؤجرة')->count();
                                                        return round(($rented / $total) * 100) . '%';
                                                    })
                                                    ->badge()
                                                    ->color('primary'),
                                            ]),
                                    ]),
                                Infolists\Components\Section::make('إحصائيات العقود والصيانة')
                                    ->schema([
                                        Infolists\Components\Grid::make(4)
                                            ->schema([
                                                Infolists\Components\TextEntry::make('report_active_contracts')
                                                    ->label('عقود نشطة')
                                                    ->state(fn ($record) => $record->contracts()->where('contracts.status', 'نشط')->count())
                                                    ->badge()
                                                    ->color('success'),
                                                Infolists\Components\TextEntry::make('report_expired_contracts')
                                                    ->label('عقود منتهية')
                                                    ->state(fn ($record) => $record->contracts()->where('contracts.status', 'منتهي')->count())
                                                    ->badge()
                                                    ->color('gray'),
                                                Infolists\Components\TextEntry::make('report_monthly_rent')
                                                    ->label('إجمالي الإيجارات')
                                                    ->state(fn ($record) => number_format($record->contracts()->where('contracts.status', 'نشط')->sum('rent_amount'), 2) . ' ر.س')
                                                    ->badge()
                                                    ->color('info'),
                                                Infolists\Components\TextEntry::make('report_maintenance_cost')
                                                    ->label('تكاليف الصيانة')
                                                    ->state(fn ($record) => number_format($record->maintenances()->sum('cost'), 2) . ' ر.س')
                                                    ->badge()
                                                    ->color('warning'),
                                            ]),
                                    ]),
                            ]),
                    ])
                    ->columnSpanFull()
                    ->persistTabInQueryString(),
            ]);
    }

    public function getRelationManagers(): array
    {
        return [
            RelationManagers\UnitsRelationManager::class,
            RelationManagers\ContractsRelationManager::class,
            RelationManagers\RevenuesRelationManager::class,
            RelationManagers\ExpensesRelationManager::class,
            RelationManagers\MaintenancesRelationManager::class,
        ];
    }
}
