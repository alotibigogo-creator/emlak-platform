<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContractResource\Pages;
use App\Filament\Resources\ContractResource\RelationManagers;
use App\Models\Contract;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ContractResource extends Resource
{
    protected static ?string $model = Contract::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'العقود';

    protected static ?string $modelLabel = 'عقد';

    protected static ?string $pluralModelLabel = 'العقود';

    protected static ?string $navigationGroup = 'العقارات';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('code')
                    ->label('رقم العقد')
                    ->disabled()
                    ->dehydrated(false)
                    ->placeholder('سيتم إنشاؤه تلقائياً'),
                Forms\Components\Select::make('unit_id')
                    ->label('الوحدة')
                    ->relationship('unit', 'code')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->code . ' - ' . $record->property->name),
                Forms\Components\Select::make('customer_id')
                    ->label('العميل')
                    ->relationship('customer', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->createOptionForm([
                        Forms\Components\TextInput::make('name')
                            ->label('الاسم')
                            ->required(),
                        Forms\Components\TextInput::make('phone')
                            ->label('رقم الجوال')
                            ->tel()
                            ->required(),
                        Forms\Components\TextInput::make('email')
                            ->label('البريد الإلكتروني')
                            ->email(),
                        Forms\Components\TextInput::make('id_number')
                            ->label('رقم الهوية')
                            ->required(),
                        Forms\Components\TextInput::make('nationality')
                            ->label('الجنسية')
                            ->required(),
                    ]),
                Forms\Components\DatePicker::make('start_date')
                    ->label('تاريخ البداية')
                    ->required()
                    ->native(false)
                    ->displayFormat('Y-m-d')
                    ->default(now()),
                Forms\Components\DatePicker::make('end_date')
                    ->label('تاريخ الانتهاء')
                    ->required()
                    ->native(false)
                    ->displayFormat('Y-m-d')
                    ->after('start_date'),
                Forms\Components\TextInput::make('rent_amount')
                    ->label('قيمة الإيجار')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->prefix('ر.س')
                    ->suffix('ريال'),
                Forms\Components\Select::make('payment_frequency')
                    ->label('دورية الدفع')
                    ->required()
                    ->options([
                        'شهري' => 'شهري',
                        'ربع سنوي' => 'ربع سنوي',
                        'نصف سنوي' => 'نصف سنوي',
                        'سنوي' => 'سنوي',
                    ])
                    ->default('شهري'),
                Forms\Components\TextInput::make('deposit')
                    ->label('مبلغ التأمين')
                    ->numeric()
                    ->minValue(0)
                    ->prefix('ر.س')
                    ->suffix('ريال'),
                Forms\Components\Select::make('status')
                    ->label('الحالة')
                    ->required()
                    ->options([
                        'نشط' => 'نشط',
                        'منتهي' => 'منتهي',
                        'ملغي' => 'ملغي',
                        'معلق' => 'معلق',
                    ])
                    ->default('نشط'),
            ]);
    }

    public static function table(Table $table): Table
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
                    ->sortable(),
                Tables\Columns\TextColumn::make('customer.name')
                    ->label('العميل')
                    ->searchable()
                    ->sortable(),
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
                    ->numeric()
                    ->sortable()
                    ->money('SAR'),
                Tables\Columns\TextColumn::make('payment_frequency')
                    ->label('دورية الدفع')
                    ->searchable()
                    ->badge(),
                Tables\Columns\TextColumn::make('deposit')
                    ->label('التأمين')
                    ->numeric()
                    ->sortable()
                    ->money('SAR'),
                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->searchable()
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'نشط' => 'success',
                        'منتهي' => 'gray',
                        'ملغي' => 'danger',
                        'معلق' => 'warning',
                        default => 'gray',
                    }),
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
                        'نشط' => 'نشط',
                        'منتهي' => 'منتهي',
                        'ملغي' => 'ملغي',
                        'معلق' => 'معلق',
                    ]),
                Tables\Filters\SelectFilter::make('payment_frequency')
                    ->label('دورية الدفع')
                    ->options([
                        'شهري' => 'شهري',
                        'ربع سنوي' => 'ربع سنوي',
                        'نصف سنوي' => 'نصف سنوي',
                        'سنوي' => 'سنوي',
                    ]),
                Tables\Filters\Filter::make('active')
                    ->label('العقود النشطة')
                    ->query(fn (Builder $query): Builder => $query->where('status', 'نشط')),
                Tables\Filters\Filter::make('expired')
                    ->label('العقود المنتهية')
                    ->query(fn (Builder $query): Builder => $query->where('end_date', '<', now())),
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
            'index' => Pages\ListContracts::route('/'),
            'create' => Pages\CreateContract::route('/create'),
            'view' => Pages\ViewContract::route('/{record}'),
            'edit' => Pages\EditContract::route('/{record}/edit'),
        ];
    }
}
