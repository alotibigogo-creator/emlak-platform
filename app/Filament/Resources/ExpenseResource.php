<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExpenseResource\Pages;
use App\Filament\Resources\ExpenseResource\RelationManagers;
use App\Models\Expense;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ExpenseResource extends Resource
{
    protected static ?string $model = Expense::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $navigationLabel = 'المصروفات';

    protected static ?string $modelLabel = 'مصروف';

    protected static ?string $pluralModelLabel = 'المصروفات';

    protected static ?string $navigationGroup = 'العقارات';

    protected static ?int $navigationSort = 6;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('code')
                    ->label('رقم المصروف')
                    ->disabled()
                    ->dehydrated(false)
                    ->placeholder('سيتم إنشاؤه تلقائياً'),
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
                    ->minValue(0)
                    ->prefix('ر.س')
                    ->suffix('ريال'),
                Forms\Components\DatePicker::make('date')
                    ->label('التاريخ')
                    ->required()
                    ->native(false)
                    ->displayFormat('Y-m-d')
                    ->default(now()),
                Forms\Components\Textarea::make('description')
                    ->label('الوصف')
                    ->rows(3)
                    ->columnSpanFull(),
                Forms\Components\Select::make('property_id')
                    ->label('العقار')
                    ->relationship('property', 'name')
                    ->searchable()
                    ->preload(),
                Forms\Components\Toggle::make('is_recurring')
                    ->label('مصروف متكرر')
                    ->live()
                    ->default(false),
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

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('رقم المصروف')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('النوع')
                    ->searchable()
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'صيانة' => 'warning',
                        'كهرباء' => 'info',
                        'ماء' => 'info',
                        'نظافة' => 'success',
                        'أمن' => 'danger',
                        'تأمينات' => 'gray',
                        'رواتب' => 'primary',
                        'ضرائب' => 'gray',
                        'أخرى' => 'gray',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('amount')
                    ->label('المبلغ')
                    ->numeric()
                    ->sortable()
                    ->money('SAR'),
                Tables\Columns\TextColumn::make('date')
                    ->label('التاريخ')
                    ->date('Y-m-d')
                    ->sortable(),
                Tables\Columns\TextColumn::make('property.name')
                    ->label('العقار')
                    ->searchable()
                    ->sortable()
                    ->default('غير محدد'),
                Tables\Columns\IconColumn::make('is_recurring')
                    ->label('متكرر')
                    ->boolean(),
                Tables\Columns\TextColumn::make('frequency')
                    ->label('التكرار')
                    ->searchable()
                    ->default('غير متكرر'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('نوع المصروف')
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
                Tables\Filters\SelectFilter::make('property_id')
                    ->label('العقار')
                    ->relationship('property', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\TernaryFilter::make('is_recurring')
                    ->label('متكرر')
                    ->boolean()
                    ->trueLabel('مصروفات متكررة')
                    ->falseLabel('مصروفات غير متكررة')
                    ->native(false),
                Tables\Filters\Filter::make('date')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label('من تاريخ'),
                        Forms\Components\DatePicker::make('until')
                            ->label('إلى تاريخ'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date', '<=', $date),
                            );
                    }),
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
            'index' => Pages\ListExpenses::route('/'),
            'create' => Pages\CreateExpense::route('/create'),
            'view' => Pages\ViewExpense::route('/{record}'),
            'edit' => Pages\EditExpense::route('/{record}/edit'),
        ];
    }
}
