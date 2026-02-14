<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MaintenanceResource\Pages;
use App\Filament\Resources\MaintenanceResource\RelationManagers;
use App\Models\Maintenance;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MaintenanceResource extends Resource
{
    protected static ?string $model = Maintenance::class;

    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';

    protected static ?string $navigationLabel = 'الصيانة';

    protected static ?string $modelLabel = 'طلب صيانة';

    protected static ?string $pluralModelLabel = 'الصيانة';

    protected static ?string $navigationGroup = 'العقارات';

    protected static ?int $navigationSort = 7;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('code')
                    ->label('رقم الطلب')
                    ->disabled()
                    ->dehydrated(false)
                    ->placeholder('سيتم إنشاؤه تلقائياً'),
                Forms\Components\Select::make('property_id')
                    ->label('العقار')
                    ->relationship('property', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->live(),
                Forms\Components\Select::make('unit_id')
                    ->label('الوحدة (اختياري)')
                    ->relationship(
                        'unit',
                        'code',
                        fn (Builder $query, $get) => $query->when(
                            $get('property_id'),
                            fn ($q, $propertyId) => $q->where('property_id', $propertyId)
                        )
                    )
                    ->searchable()
                    ->preload()
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->code . ' - ' . $record->number),
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
                    ->rows(4)
                    ->columnSpanFull(),
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
                    ->minValue(0)
                    ->prefix('ر.س')
                    ->suffix('ريال'),
                Forms\Components\DatePicker::make('date')
                    ->label('تاريخ الطلب')
                    ->required()
                    ->native(false)
                    ->displayFormat('Y-m-d')
                    ->default(now()),
                Forms\Components\DateTimePicker::make('completed_at')
                    ->label('تاريخ الإنجاز')
                    ->native(false)
                    ->displayFormat('Y-m-d H:i'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('رقم الطلب')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('property.name')
                    ->label('العقار')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('unit.code')
                    ->label('الوحدة')
                    ->searchable()
                    ->sortable()
                    ->default('غير محدد'),
                Tables\Columns\TextColumn::make('type')
                    ->label('النوع')
                    ->searchable()
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'كهرباء' => 'warning',
                        'سباكة' => 'info',
                        'تكييف' => 'primary',
                        'نجارة' => 'gray',
                        'دهانات' => 'success',
                        'نظافة' => 'success',
                        'أخرى' => 'gray',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->searchable()
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
                    ->searchable()
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
                    ->money('SAR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('date')
                    ->label('تاريخ الطلب')
                    ->date('Y-m-d')
                    ->sortable(),
                Tables\Columns\TextColumn::make('completed_at')
                    ->label('تاريخ الإنجاز')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->placeholder('غير مكتمل'),
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
                Tables\Filters\SelectFilter::make('type')
                    ->label('النوع')
                    ->options([
                        'كهرباء' => 'كهرباء',
                        'سباكة' => 'سباكة',
                        'تكييف' => 'تكييف',
                        'نجارة' => 'نجارة',
                        'دهانات' => 'دهانات',
                        'نظافة' => 'نظافة',
                        'أخرى' => 'أخرى',
                    ]),
                Tables\Filters\SelectFilter::make('property_id')
                    ->label('العقار')
                    ->relationship('property', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\Filter::make('pending')
                    ->label('قيد المعالجة')
                    ->query(fn (Builder $query): Builder => $query->whereIn('status', ['معلقة', 'قيد التنفيذ'])),
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
            'index' => Pages\ListMaintenances::route('/'),
            'create' => Pages\CreateMaintenance::route('/create'),
            'view' => Pages\ViewMaintenance::route('/{record}'),
            'edit' => Pages\EditMaintenance::route('/{record}/edit'),
        ];
    }
}
