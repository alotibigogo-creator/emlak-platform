<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UnitResource\Pages;
use App\Filament\Resources\UnitResource\RelationManagers;
use App\Models\Unit;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UnitResource extends Resource
{
    protected static ?string $model = Unit::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office';

    protected static ?string $navigationLabel = 'الوحدات';

    protected static ?string $modelLabel = 'وحدة';

    protected static ?string $pluralModelLabel = 'الوحدات';

    protected static ?string $navigationGroup = 'العقارات';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('code')
                    ->label('رمز الوحدة')
                    ->disabled()
                    ->dehydrated(false)
                    ->placeholder('سيتم إنشاؤه تلقائياً'),
                Forms\Components\Select::make('property_id')
                    ->label('العقار')
                    ->relationship('property', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('type')
                    ->label('نوع الوحدة')
                    ->required()
                    ->options([
                        'شقة' => 'شقة',
                        'فيلا' => 'فيلا',
                        'دور' => 'دور',
                        'محل تجاري' => 'محل تجاري',
                        'مكتب' => 'مكتب',
                        'مستودع' => 'مستودع',
                        'أرض' => 'أرض',
                        'استراحة' => 'استراحة',
                    ]),
                Forms\Components\TextInput::make('floor')
                    ->label('الطابق')
                    ->numeric()
                    ->minValue(0),
                Forms\Components\TextInput::make('number')
                    ->label('رقم الوحدة')
                    ->maxLength(255),
                Forms\Components\TextInput::make('area')
                    ->label('المساحة (م²)')
                    ->numeric()
                    ->minValue(0)
                    ->suffix('م²'),
                Forms\Components\TextInput::make('bedrooms')
                    ->label('عدد الغرف')
                    ->numeric()
                    ->minValue(0),
                Forms\Components\TextInput::make('bathrooms')
                    ->label('عدد الحمامات')
                    ->numeric()
                    ->minValue(0),
                Forms\Components\Select::make('status')
                    ->label('الحالة')
                    ->required()
                    ->options([
                        'متاحة' => 'متاحة',
                        'مؤجرة' => 'مؤجرة',
                        'صيانة' => 'صيانة',
                        'محجوزة' => 'محجوزة',
                    ])
                    ->default('متاحة'),
                Forms\Components\TextInput::make('rent_price')
                    ->label('سعر الإيجار')
                    ->numeric()
                    ->minValue(0)
                    ->prefix('ر.س')
                    ->suffix('ريال'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('رمز الوحدة')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('property.name')
                    ->label('العقار')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('النوع')
                    ->searchable()
                    ->badge(),
                Tables\Columns\TextColumn::make('floor')
                    ->label('الطابق')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('number')
                    ->label('رقم الوحدة')
                    ->searchable(),
                Tables\Columns\TextColumn::make('area')
                    ->label('المساحة')
                    ->numeric()
                    ->sortable()
                    ->suffix(' م²'),
                Tables\Columns\TextColumn::make('bedrooms')
                    ->label('الغرف')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('bathrooms')
                    ->label('الحمامات')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->searchable()
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'متاحة' => 'success',
                        'مؤجرة' => 'warning',
                        'صيانة' => 'danger',
                        'محجوزة' => 'info',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('rent_price')
                    ->label('سعر الإيجار')
                    ->numeric()
                    ->sortable()
                    ->money('SAR'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'متاحة' => 'متاحة',
                        'مؤجرة' => 'مؤجرة',
                        'صيانة' => 'صيانة',
                        'محجوزة' => 'محجوزة',
                    ]),
                Tables\Filters\SelectFilter::make('type')
                    ->label('نوع الوحدة')
                    ->options([
                        'شقة' => 'شقة',
                        'فيلا' => 'فيلا',
                        'دور' => 'دور',
                        'محل تجاري' => 'محل تجاري',
                        'مكتب' => 'مكتب',
                        'مستودع' => 'مستودع',
                        'أرض' => 'أرض',
                        'استراحة' => 'استراحة',
                    ]),
                Tables\Filters\SelectFilter::make('property_id')
                    ->label('العقار')
                    ->relationship('property', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->label('عرض'),
                Tables\Actions\EditAction::make()->label('تعديل'),
                Tables\Actions\DeleteAction::make()->label('حذف'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label('حذف المحدد'),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUnits::route('/'),
            'create' => Pages\CreateUnit::route('/create'),
            'view' => Pages\ViewUnit::route('/{record}'),
            'edit' => Pages\EditUnit::route('/{record}/edit'),
        ];
    }
}
