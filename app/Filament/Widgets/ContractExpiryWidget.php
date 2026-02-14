<?php

namespace App\Filament\Widgets;

use App\Models\Contract;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class ContractExpiryWidget extends BaseWidget
{
    protected static bool $isLazy = true;

    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('العقود المنتهية قريباً')
            ->description('العقود التي ستنتهي خلال 30 يوماً')
            ->query(
                Contract::query()
                    ->where('status', 'نشط')
                    ->whereBetween('end_date', [now(), now()->addDays(30)])
                    ->orderBy('end_date')
            )
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('رقم العقد')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('unit.code')
                    ->label('الوحدة')
                    ->searchable()
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('customer.name')
                    ->label('المستأجر')
                    ->searchable()
                    ->limit(30),
                Tables\Columns\TextColumn::make('end_date')
                    ->label('تاريخ الانتهاء')
                    ->date('Y-m-d')
                    ->sortable()
                    ->badge()
                    ->color(fn ($record) => $record->end_date->diffInDays(now()) <= 7 ? 'danger' : 'warning'),
                Tables\Columns\TextColumn::make('days_remaining')
                    ->label('المتبقي')
                    ->state(fn ($record) =>
                        $record->end_date->diffInDays(now()) . ' يوم'
                    )
                    ->badge()
                    ->color(fn ($record) =>
                        $record->end_date->diffInDays(now()) <= 7 ? 'danger' :
                        ($record->end_date->diffInDays(now()) <= 15 ? 'warning' : 'success')
                    ),
                Tables\Columns\TextColumn::make('rent_amount')
                    ->label('قيمة الإيجار')
                    ->money('SAR')
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('renew')
                    ->label('تجديد')
                    ->icon('heroicon-o-arrow-path')
                    ->color('success')
                    ->url(fn (Contract $record): string => route('filament.admin.resources.contracts.edit', $record)),
            ]);
    }
}
