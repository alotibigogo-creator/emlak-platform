<?php

namespace App\Filament\Resources\PropertyResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class RevenuesRelationManager extends RelationManager
{
    protected static string $relationship = 'revenues';

    protected static ?string $title = 'الإيرادات';

    protected static ?string $modelLabel = 'إيراد';

    protected static ?string $pluralModelLabel = 'الإيرادات';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('type')
                    ->label('نوع الإيراد')
                    ->required()
                    ->options([
                        'إيجار' => 'إيجار',
                        'تأمين' => 'تأمين',
                        'خدمات' => 'خدمات',
                        'غرامات تأخير' => 'غرامات تأخير',
                        'صيانة' => 'صيانة',
                        'أخرى' => 'أخرى',
                    ]),
                Forms\Components\TextInput::make('amount')
                    ->label('المبلغ')
                    ->required()
                    ->numeric()
                    ->prefix('ر.س'),
                Forms\Components\DatePicker::make('date')
                    ->label('التاريخ')
                    ->required()
                    ->default(now()),
                Forms\Components\Textarea::make('description')
                    ->label('الوصف')
                    ->rows(3),
                Forms\Components\Select::make('contract_id')
                    ->label('العقد')
                    ->relationship('contract', 'code')
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('status')
                    ->label('الحالة')
                    ->required()
                    ->options([
                        'مدفوعة' => 'مدفوعة',
                        'معلقة' => 'معلقة',
                        'ملغاة' => 'ملغاة',
                    ])
                    ->default('مدفوعة'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('الرمز')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('النوع')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'إيجار' => 'success',
                        'تأمين' => 'info',
                        'خدمات' => 'warning',
                        'غرامات تأخير' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('amount')
                    ->label('المبلغ')
                    ->money('SAR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('date')
                    ->label('التاريخ')
                    ->date('Y-m-d')
                    ->sortable(),
                Tables\Columns\TextColumn::make('contract.code')
                    ->label('العقد')
                    ->badge(),
                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'مدفوعة' => 'success',
                        'معلقة' => 'warning',
                        'ملغاة' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'مدفوعة' => 'مدفوعة',
                        'معلقة' => 'معلقة',
                        'ملغاة' => 'ملغاة',
                    ]),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->label('إضافة إيراد'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('تعديل'),
                Tables\Actions\DeleteAction::make()->label('حذف'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label('حذف المحدد'),
                ]),
            ]);
    }
}
