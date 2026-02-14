<?php

namespace App\Filament\Resources\PropertyResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class MaintenancesRelationManager extends RelationManager
{
    protected static string $relationship = 'maintenances';

    protected static ?string $title = 'الصيانة';

    protected static ?string $modelLabel = 'طلب صيانة';

    protected static ?string $pluralModelLabel = 'الصيانة';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('unit_id')
                    ->label('الوحدة')
                    ->relationship('unit', 'code')
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('type')
                    ->label('نوع الصيانة')
                    ->required()
                    ->options([
                        'كهرباء' => 'كهرباء',
                        'سباكة' => 'سباكة',
                        'تكييف' => 'تكييف',
                        'نجارة' => 'نجارة',
                        'دهانات' => 'دهانات',
                        'نظافة' => 'نظافة',
                        'أخرى' => 'أخرى',
                    ]),
                Forms\Components\Textarea::make('description')
                    ->label('وصف المشكلة')
                    ->required()
                    ->rows(4),
                Forms\Components\Select::make('status')
                    ->label('الحالة')
                    ->required()
                    ->options([
                        'معلقة' => 'معلقة',
                        'قيد التنفيذ' => 'قيد التنفيذ',
                        'مكتملة' => 'مكتملة',
                        'ملغاة' => 'ملغاة',
                    ])
                    ->default('معلقة'),
                Forms\Components\Select::make('priority')
                    ->label('الأولوية')
                    ->required()
                    ->options([
                        'منخفضة' => 'منخفضة',
                        'متوسطة' => 'متوسطة',
                        'عالية' => 'عالية',
                        'عاجلة' => 'عاجلة',
                    ])
                    ->default('متوسطة'),
                Forms\Components\TextInput::make('cost')
                    ->label('التكلفة')
                    ->numeric()
                    ->prefix('ر.س'),
                Forms\Components\DatePicker::make('date')
                    ->label('تاريخ الطلب')
                    ->required()
                    ->default(now()),
                Forms\Components\DateTimePicker::make('completed_at')
                    ->label('تاريخ الإنجاز'),
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
                Tables\Columns\TextColumn::make('unit.code')
                    ->label('الوحدة')
                    ->badge()
                    ->default('عام'),
                Tables\Columns\TextColumn::make('type')
                    ->label('النوع')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'كهرباء' => 'warning',
                        'سباكة' => 'info',
                        'تكييف' => 'primary',
                        'نجارة' => 'gray',
                        'دهانات' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'معلقة' => 'warning',
                        'قيد التنفيذ' => 'info',
                        'مكتملة' => 'success',
                        'ملغاة' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('priority')
                    ->label('الأولوية')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'منخفضة' => 'success',
                        'متوسطة' => 'info',
                        'عالية' => 'warning',
                        'عاجلة' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('cost')
                    ->label('التكلفة')
                    ->money('SAR'),
                Tables\Columns\TextColumn::make('date')
                    ->label('التاريخ')
                    ->date('Y-m-d')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'معلقة' => 'معلقة',
                        'قيد التنفيذ' => 'قيد التنفيذ',
                        'مكتملة' => 'مكتملة',
                        'ملغاة' => 'ملغاة',
                    ]),
                Tables\Filters\SelectFilter::make('priority')
                    ->label('الأولوية')
                    ->options([
                        'منخفضة' => 'منخفضة',
                        'متوسطة' => 'متوسطة',
                        'عالية' => 'عالية',
                        'عاجلة' => 'عاجلة',
                    ]),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->label('إضافة طلب صيانة'),
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
