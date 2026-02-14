<?php

namespace App\Filament\Widgets;

use Spatie\Activitylog\Models\Activity;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentActivitiesWidget extends BaseWidget
{
    protected static bool $isLazy = true;

    protected static ?int $sort = 7;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('آخر النشاطات')
            ->description('سجل آخر 10 عمليات تمت في النظام')
            ->query(
                Activity::query()
                    ->latest()
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('description')
                    ->label('العملية')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'created' => 'إنشاء',
                        'updated' => 'تعديل',
                        'deleted' => 'حذف',
                        default => $state,
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'created' => 'success',
                        'updated' => 'info',
                        'deleted' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('subject_type')
                    ->label('نوع العنصر')
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'App\\Models\\Owner' => 'مالك',
                        'App\\Models\\Property' => 'عقار',
                        'App\\Models\\Unit' => 'وحدة',
                        'App\\Models\\Customer' => 'عميل',
                        'App\\Models\\Contract' => 'عقد',
                        'App\\Models\\Revenue' => 'إيراد',
                        'App\\Models\\Expense' => 'مصروف',
                        'App\\Models\\Maintenance' => 'صيانة',
                        default => $state ?? 'غير محدد',
                    })
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('subject_id')
                    ->label('رقم العنصر'),
                Tables\Columns\TextColumn::make('causer.name')
                    ->label('بواسطة')
                    ->default('النظام'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('الوقت')
                    ->since()
                    ->sortable(),
            ]);
    }
}
