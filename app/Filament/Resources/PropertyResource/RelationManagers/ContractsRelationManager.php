<?php

namespace App\Filament\Resources\PropertyResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ContractsRelationManager extends RelationManager
{
    protected static string $relationship = 'contracts';

    protected static ?string $title = 'العقود';

    protected static ?string $modelLabel = 'عقد';

    protected static ?string $pluralModelLabel = 'العقود';

    public function table(Table $table): Table
    {
        return $table
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
                Tables\Columns\TextColumn::make('start_date')
                    ->label('تاريخ البداية')
                    ->date('Y-m-d')
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_date')
                    ->label('تاريخ الانتهاء')
                    ->date('Y-m-d')
                    ->sortable(),
                Tables\Columns\TextColumn::make('rent_amount')
                    ->label('قيمة الإيجار')
                    ->money('SAR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('payment_frequency')
                    ->label('دورية الدفع')
                    ->badge(),
                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'نشط' => 'success',
                        'منتهي' => 'gray',
                        'ملغي' => 'danger',
                        'معلق' => 'warning',
                        default => 'gray',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'نشط' => 'نشط',
                        'منتهي' => 'منتهي',
                        'ملغي' => 'ملغي',
                        'معلق' => 'معلق',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->label('عرض'),
            ])
            ->bulkActions([]);
    }
}
