<?php

namespace App\Filament\Widgets;

use App\Models\PaymentSchedule;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class OutstandingPaymentsWidget extends BaseWidget
{
    protected static bool $isLazy = true;

    protected static ?int $sort = 5;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('المدفوعات المستحقة')
            ->description('الدفعات المعلقة والمتأخرة')
            ->query(
                PaymentSchedule::query()
                    ->where('status', 'معلقة')
                    ->where('due_date', '<=', now()->addDays(7))
                    ->orderBy('due_date')
            )
            ->columns([
                Tables\Columns\TextColumn::make('contract.code')
                    ->label('العقد')
                    ->searchable()
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('contract.customer.name')
                    ->label('المستأجر')
                    ->searchable()
                    ->limit(25),
                Tables\Columns\TextColumn::make('contract.unit.code')
                    ->label('الوحدة')
                    ->searchable(),
                Tables\Columns\TextColumn::make('due_date')
                    ->label('تاريخ الاستحقاق')
                    ->date('Y-m-d')
                    ->sortable()
                    ->badge()
                    ->color(fn ($record) => $record->due_date->isPast() ? 'danger' : 'warning'),
                Tables\Columns\TextColumn::make('amount')
                    ->label('المبلغ')
                    ->money('SAR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->formatStateUsing(fn ($record) =>
                        $record->due_date->isPast() ? 'متأخرة' : 'معلقة'
                    )
                    ->color(fn ($record) =>
                        $record->due_date->isPast() ? 'danger' : 'warning'
                    ),
            ])
            ->actions([
                Tables\Actions\Action::make('mark_paid')
                    ->label('تحديد كمدفوعة')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (PaymentSchedule $record) {
                        $record->update([
                            'status' => 'مدفوعة',
                            'paid_at' => now(),
                        ]);
                    }),
            ]);
    }
}
