<?php

namespace App\Filament\Resources\PropertyResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class UnitsRelationManager extends RelationManager
{
    protected static string $relationship = 'units';

    protected static ?string $title = 'الوحدات';

    protected static ?string $modelLabel = 'وحدة';

    protected static ?string $pluralModelLabel = 'الوحدات';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('code')
                    ->label('رمز الوحدة')
                    ->disabled()
                    ->placeholder('سيتم إنشاؤه تلقائياً'),
                Forms\Components\Select::make('type')
                    ->label('نوع الوحدة')
                    ->required()
                    ->options([
                        'شقة' => 'شقة',
                        'فيلا' => 'فيلا',
                        'محل' => 'محل',
                        'مكتب' => 'مكتب',
                        'مستودع' => 'مستودع',
                    ]),
                Forms\Components\TextInput::make('floor')
                    ->label('الطابق')
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('number')
                    ->label('رقم الوحدة')
                    ->required(),
                Forms\Components\TextInput::make('area')
                    ->label('المساحة')
                    ->numeric()
                    ->suffix('متر مربع'),
                Forms\Components\TextInput::make('bedrooms')
                    ->label('عدد الغرف')
                    ->numeric(),
                Forms\Components\TextInput::make('bathrooms')
                    ->label('عدد الحمامات')
                    ->numeric(),
                Forms\Components\Select::make('status')
                    ->label('الحالة')
                    ->required()
                    ->options([
                        'متاحة' => 'متاحة',
                        'مؤجرة' => 'مؤجرة',
                        'صيانة' => 'صيانة',
                    ])
                    ->default('متاحة'),
                Forms\Components\TextInput::make('rent_price')
                    ->label('سعر الإيجار')
                    ->numeric()
                    ->prefix('ر.س'),
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
                    ->badge(),
                Tables\Columns\TextColumn::make('number')
                    ->label('رقم الوحدة')
                    ->searchable(),
                Tables\Columns\TextColumn::make('floor')
                    ->label('الطابق')
                    ->sortable(),
                Tables\Columns\TextColumn::make('area')
                    ->label('المساحة')
                    ->suffix(' م²'),
                Tables\Columns\TextColumn::make('bedrooms')
                    ->label('الغرف'),
                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'متاحة' => 'success',
                        'مؤجرة' => 'warning',
                        'صيانة' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('rent_price')
                    ->label('الإيجار')
                    ->money('SAR'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'متاحة' => 'متاحة',
                        'مؤجرة' => 'مؤجرة',
                        'صيانة' => 'صيانة',
                    ]),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->label('إضافة وحدة'),
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
