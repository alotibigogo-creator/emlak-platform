<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NotificationResource\Pages;
use App\Filament\Resources\NotificationResource\RelationManagers;
use App\Models\Notification;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class NotificationResource extends Resource
{
    protected static ?string $model = Notification::class;

    protected static ?string $navigationIcon = 'heroicon-o-bell-alert';

    protected static ?string $navigationLabel = 'الإشعارات';

    protected static ?string $modelLabel = 'إشعار';

    protected static ?string $pluralModelLabel = 'الإشعارات';

    protected static ?string $navigationGroup = 'النظام';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationBadgeTooltip = 'إشعارات غير مقروءة';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('is_read', false)->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $count = static::getModel()::where('is_read', false)->count();
        return $count > 0 ? 'danger' : null;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('type')
                    ->label('نوع الإشعار')
                    ->required()
                    ->options([
                        'contract_expiry' => 'انتهاء عقد',
                        'payment_due' => 'دفعة مستحقة',
                        'maintenance_urgent' => 'صيانة عاجلة',
                        'maintenance_completed' => 'صيانة مكتملة',
                        'general' => 'عام',
                    ]),
                Forms\Components\TextInput::make('title')
                    ->label('العنوان')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('message')
                    ->label('الرسالة')
                    ->required()
                    ->rows(4)
                    ->columnSpanFull(),
                Forms\Components\Toggle::make('is_read')
                    ->label('مقروء')
                    ->default(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\IconColumn::make('is_read')
                    ->label('الحالة')
                    ->boolean()
                    ->trueIcon('heroicon-o-envelope-open')
                    ->falseIcon('heroicon-o-envelope')
                    ->trueColor('success')
                    ->falseColor('warning'),
                Tables\Columns\TextColumn::make('type')
                    ->label('النوع')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'contract_expiry' => 'انتهاء عقد',
                        'payment_due' => 'دفعة مستحقة',
                        'maintenance_urgent' => 'صيانة عاجلة',
                        'maintenance_completed' => 'صيانة مكتملة',
                        'general' => 'عام',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'contract_expiry' => 'warning',
                        'payment_due' => 'danger',
                        'maintenance_urgent' => 'danger',
                        'maintenance_completed' => 'success',
                        'general' => 'info',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('title')
                    ->label('العنوان')
                    ->searchable()
                    ->weight('bold')
                    ->wrap(),
                Tables\Columns\TextColumn::make('message')
                    ->label('الرسالة')
                    ->searchable()
                    ->limit(50)
                    ->wrap(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('التاريخ')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->since(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_read')
                    ->label('الحالة')
                    ->placeholder('الكل')
                    ->trueLabel('مقروء')
                    ->falseLabel('غير مقروء')
                    ->queries(
                        true: fn (Builder $query) => $query->where('is_read', true),
                        false: fn (Builder $query) => $query->where('is_read', false),
                    )
                    ->native(false),
                Tables\Filters\SelectFilter::make('type')
                    ->label('نوع الإشعار')
                    ->options([
                        'contract_expiry' => 'انتهاء عقد',
                        'payment_due' => 'دفعة مستحقة',
                        'maintenance_urgent' => 'صيانة عاجلة',
                        'maintenance_completed' => 'صيانة مكتملة',
                        'general' => 'عام',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('mark_as_read')
                    ->label('تحديد كمقروء')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn (Notification $record) => !$record->is_read)
                    ->action(fn (Notification $record) => $record->markAsRead()),
                Tables\Actions\EditAction::make()->label('تعديل'),
                Tables\Actions\DeleteAction::make()->label('حذف'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('mark_as_read')
                        ->label('تحديد كمقروء')
                        ->icon('heroicon-o-check')
                        ->action(fn ($records) => $records->each->markAsRead())
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\DeleteBulkAction::make()->label('حذف المحدد'),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageNotifications::route('/'),
        ];
    }
}
