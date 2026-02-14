<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerResource\Pages;
use App\Filament\Resources\CustomerResource\RelationManagers;
use App\Models\Customer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'العملاء';

    protected static ?string $modelLabel = 'عميل';

    protected static ?string $pluralModelLabel = 'العملاء';

    protected static ?string $navigationGroup = 'العملاء';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('الاسم')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('phone')
                    ->label('رقم الجوال')
                    ->tel()
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->label('البريد الإلكتروني')
                    ->email()
                    ->maxLength(255),
                Forms\Components\TextInput::make('id_number')
                    ->label('رقم الهوية')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                Forms\Components\Select::make('nationality')
                    ->label('الجنسية')
                    ->required()
                    ->options([
                        'سعودي' => 'سعودي',
                        'مصري' => 'مصري',
                        'سوري' => 'سوري',
                        'يمني' => 'يمني',
                        'أردني' => 'أردني',
                        'فلسطيني' => 'فلسطيني',
                        'لبناني' => 'لبناني',
                        'سوداني' => 'سوداني',
                        'هندي' => 'هندي',
                        'باكستاني' => 'باكستاني',
                        'بنغلاديشي' => 'بنغلاديشي',
                        'فلبيني' => 'فلبيني',
                        'أخرى' => 'أخرى',
                    ])
                    ->searchable(),
                Forms\Components\Textarea::make('address')
                    ->label('العنوان')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('الاسم')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label('رقم الجوال')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('البريد الإلكتروني')
                    ->searchable(),
                Tables\Columns\TextColumn::make('id_number')
                    ->label('رقم الهوية')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nationality')
                    ->label('الجنسية')
                    ->searchable()
                    ->badge(),
                Tables\Columns\TextColumn::make('contracts_count')
                    ->label('عدد العقود')
                    ->counts('contracts')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('nationality')
                    ->label('الجنسية')
                    ->options([
                        'سعودي' => 'سعودي',
                        'مصري' => 'مصري',
                        'سوري' => 'سوري',
                        'يمني' => 'يمني',
                        'أردني' => 'أردني',
                        'فلسطيني' => 'فلسطيني',
                        'لبناني' => 'لبناني',
                        'سوداني' => 'سوداني',
                        'هندي' => 'هندي',
                        'باكستاني' => 'باكستاني',
                        'بنغلاديشي' => 'بنغلاديشي',
                        'فلبيني' => 'فلبيني',
                        'أخرى' => 'أخرى',
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
            'index' => Pages\ListCustomers::route('/'),
            'create' => Pages\CreateCustomer::route('/create'),
            'view' => Pages\ViewCustomer::route('/{record}'),
            'edit' => Pages\EditCustomer::route('/{record}/edit'),
        ];
    }
}
