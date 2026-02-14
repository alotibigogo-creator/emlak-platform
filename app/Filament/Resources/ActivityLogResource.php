<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ActivityLogResource\Pages;
use App\Filament\Resources\ActivityLogResource\RelationManagers;
use Spatie\Activitylog\Models\Activity;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class ActivityLogResource extends Resource
{
    protected static ?string $model = Activity::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationLabel = 'سجل العمليات';

    protected static ?string $modelLabel = 'عملية';

    protected static ?string $pluralModelLabel = 'سجل العمليات';

    protected static ?string $navigationGroup = 'النظام';

    protected static ?int $navigationSort = 2;


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('description')
                    ->label('الوصف')
                    ->disabled(),
                Forms\Components\TextInput::make('subject_type')
                    ->label('نوع العنصر')
                    ->disabled(),
                Forms\Components\TextInput::make('subject_id')
                    ->label('رقم العنصر')
                    ->disabled(),
                Forms\Components\KeyValue::make('properties')
                    ->label('التفاصيل')
                    ->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('log_name')
                    ->label('نوع السجل')
                    ->badge()
                    ->default('افتراضي'),
                Tables\Columns\TextColumn::make('description')
                    ->label('الوصف')
                    ->searchable()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'created' => 'إنشاء',
                        'updated' => 'تعديل',
                        'deleted' => 'حذف',
                        default => $state,
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'created' => 'success',
                        'updated' => 'info',
                        'deleted' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('subject_type')
                    ->label('نوع العنصر')
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'App\\Models\\Owner' => 'مالك',
                        'App\\Models\\Property' => 'عقار',
                        'App\\Models\\Unit' => 'وحدة',
                        'App\\Models\\Customer' => 'عميل',
                        'App\\Models\\Contract' => 'عقد',
                        'App\\Models\\Revenue' => 'إيراد',
                        'App\\Models\\Expense' => 'مصروف',
                        'App\\Models\\Maintenance' => 'صيانة',
                        default => $state ?? 'غير محدد',
                    })
                    ->searchable()
                    ->badge(),
                Tables\Columns\TextColumn::make('subject_id')
                    ->label('رقم العنصر')
                    ->sortable(),
                Tables\Columns\TextColumn::make('causer.name')
                    ->label('المستخدم')
                    ->default('النظام')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('التاريخ')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->since(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('description')
                    ->label('نوع العملية')
                    ->options([
                        'created' => 'إنشاء',
                        'updated' => 'تعديل',
                        'deleted' => 'حذف',
                    ]),
                Tables\Filters\SelectFilter::make('subject_type')
                    ->label('نوع العنصر')
                    ->options([
                        'App\\Models\\Owner' => 'مالك',
                        'App\\Models\\Property' => 'عقار',
                        'App\\Models\\Unit' => 'وحدة',
                        'App\\Models\\Customer' => 'عميل',
                        'App\\Models\\Contract' => 'عقد',
                        'App\\Models\\Revenue' => 'إيراد',
                        'App\\Models\\Expense' => 'مصروف',
                        'App\\Models\\Maintenance' => 'صيانة',
                    ]),
                Tables\Filters\Filter::make('created_at')
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
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->label('عرض'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label('حذف المحدد'),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageActivityLogs::route('/'),
        ];
    }
}
