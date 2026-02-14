<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PropertyResource\Pages;
use App\Filament\Resources\PropertyResource\RelationManagers;
use App\Models\Property;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PropertyResource extends Resource
{
    protected static ?string $model = Property::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationLabel = 'بيانات العقار';

    protected static ?string $modelLabel = 'عقار';

    protected static ?string $pluralModelLabel = 'العقارات';

    protected static ?string $navigationGroup = 'العقارات';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('اسم العقار')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('type')
                    ->label('نوع العقار')
                    ->options([
                        'سكني' => 'سكني',
                        'تجاري' => 'تجاري',
                        'إداري' => 'إداري',
                        'أرض' => 'أرض',
                    ])
                    ->required(),
                Forms\Components\Select::make('owner_id')
                    ->label('المالك')
                    ->relationship('owner', 'name')
                    ->searchable()
                    ->preload()
                    ->createOptionForm([
                        Forms\Components\TextInput::make('name')
                            ->label('اسم المالك')
                            ->required(),
                        Forms\Components\TextInput::make('phone')
                            ->label('رقم الجوال')
                            ->tel(),
                        Forms\Components\TextInput::make('identity_number')
                            ->label('رقم الهوية'),
                    ]),
                Forms\Components\TextInput::make('area')
                    ->label('المساحة (م²)')
                    ->numeric()
                    ->suffix('م²'),
                Forms\Components\Textarea::make('address')
                    ->label('العنوان')
                    ->required()
                    ->rows(2)
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('description')
                    ->label('الوصف')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('الكود')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('success'),
                Tables\Columns\TextColumn::make('name')
                    ->label('اسم العقار')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('type')
                    ->label('النوع')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'سكني' => 'info',
                        'تجاري' => 'warning',
                        'إداري' => 'success',
                        'أرض' => 'gray',
                    }),
                Tables\Columns\TextColumn::make('owner.name')
                    ->label('المالك')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('units_count')
                    ->label('عدد الوحدات')
                    ->counts('units')
                    ->sortable(),
                Tables\Columns\TextColumn::make('area')
                    ->label('المساحة')
                    ->numeric()
                    ->suffix(' م²')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('نوع العقار')
                    ->options([
                        'سكني' => 'سكني',
                        'تجاري' => 'تجاري',
                        'إداري' => 'إداري',
                        'أرض' => 'أرض',
                    ]),
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
            'index' => Pages\ListProperties::route('/'),
            'create' => Pages\CreateProperty::route('/create'),
            'view' => Pages\ViewProperty::route('/{record}'),
            'edit' => Pages\EditProperty::route('/{record}/edit'),
        ];
    }
}
