<?php

namespace App\Filament\Resources\PropertyResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ExpensesRelationManager extends RelationManager
{
    protected static string $relationship = 'expenses';

    protected static ?string $title = 'المصروفات';

    protected static ?string $modelLabel = 'مصروف';

    protected static ?string $pluralModelLabel = 'المصروفات';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('type')
                    ->label('نوع المصروف')
                    ->required()
                    ->options([
                        'صيانة' => 'صيانة',
                        'كهرباء' => 'كهرباء',
                        'ماء' => 'ماء',
                        'نظافة' => 'نظافة',
                        'أمن' => 'أمن',
                        'تأمينات' => 'تأمينات',
                        'رواتب' => 'رواتب',
                        'ضرائب' => 'ضرائب',
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
                Forms\Components\Toggle::make('is_recurring')
                    ->label('مصروف متكرر')
                    ->default(false)
                    ->live(),
                Forms\Components\Select::make('frequency')
                    ->label('تكرار الدفع')
                    ->options([
                        'شهري' => 'شهري',
                        'ربع سنوي' => 'ربع سنوي',
                        'نصف سنوي' => 'نصف سنوي',
                        'سنوي' => 'سنوي',
                    ])
                    ->visible(fn (Forms\Get $get) => $get('is_recurring')),
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
                        'صيانة' => 'warning',
                        'كهرباء' => 'info',
                        'ماء' => 'info',
                        'نظافة' => 'success',
                        'أمن' => 'danger',
                        'رواتب' => 'primary',
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
                Tables\Columns\IconColumn::make('is_recurring')
                    ->label('متكرر')
                    ->boolean(),
                Tables\Columns\TextColumn::make('frequency')
                    ->label('التكرار')
                    ->default('غير متكرر'),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_recurring')
                    ->label('متكرر')
                    ->boolean(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->label('إضافة مصروف'),
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
