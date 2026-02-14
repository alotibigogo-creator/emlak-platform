<?php

namespace App\Filament\Widgets;

use App\Models\Notification;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class NotificationsWidget extends BaseWidget
{
    protected static bool $isLazy = true;

    protected static ?int $sort = 6;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('آخر الإشعارات')
            ->description('الإشعارات الأخيرة (غير المقروءة)')
            ->query(
                Notification::query()
                    ->where('is_read', false)
                    ->latest()
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\IconColumn::make('is_read')
                    ->label('')
                    ->boolean()
                    ->trueIcon('heroicon-o-envelope-open')
                    ->falseIcon('heroicon-o-envelope')
                    ->trueColor('gray')
                    ->falseColor('warning'),
                Tables\Columns\TextColumn::make('type')
                    ->label('النوع')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'contract_expiry' => 'انتهاء عقد',
                        'payment_due' => 'دفعة مستحقة',
                        'maintenance_urgent' => 'صيانة عاجلة',
                        'maintenance_completed' => 'صيانة مكتملة',
                        'general' => 'عام',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'contract_expiry' => 'warning',
                        'payment_due' => 'danger',
                        'maintenance_urgent' => 'danger',
                        'maintenance_completed' => 'success',
                        'general' => 'info',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('title')
                    ->label('العنوان')
                    ->searchable()
                    ->weight('bold')
                    ->limit(40),
                Tables\Columns\TextColumn::make('message')
                    ->label('الرسالة')
                    ->limit(60)
                    ->wrap(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('الوقت')
                    ->since()
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('mark_as_read')
                    ->label('تحديد كمقروء')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->action(fn (Notification $record) => $record->markAsRead()),
            ]);
    }
}
