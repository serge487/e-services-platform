<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OfficeResource\Pages;
use App\Models\Office;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class OfficeResource extends Resource
{
    protected static ?string $model = Office::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationGroup = 'Location Management';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Office Information')
                    ->schema([
                        Forms\Components\Select::make('municipality_id')
                            ->relationship('municipality', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->label('Municipality')
                            ->helperText('One government office per municipality.')
                            ->unique(Office::class, 'municipality_id', ignoreRecord: true),
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g. Civil Registry Office'),
                        Forms\Components\Textarea::make('address')
                            ->required()
                            ->rows(3)
                            ->columnSpanFull()
                            ->placeholder('Full address...'),
                        Forms\Components\TextInput::make('contact_info')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g. +961 1 234567'),
                    ])->columns(2),

                Forms\Components\Section::make('Map Location')
                    ->description('Enter the GPS coordinates for Google Maps.')
                    ->schema([
                        Forms\Components\TextInput::make('latitude')
                            ->required()
                            ->numeric()
                            ->placeholder('e.g. 33.8938')
                            ->rules(['min:-90', 'max:90']),
                        Forms\Components\TextInput::make('longitude')
                            ->required()
                            ->numeric()
                            ->placeholder('e.g. 35.5018')
                            ->rules(['min:-180', 'max:180']),
                    ])->columns(2),

                Forms\Components\Section::make('Working Hours')
                    ->description('Define working hours for each day.')
                    ->schema([
                        Forms\Components\KeyValue::make('working_hours')
                            ->keyLabel('Day')
                            ->valueLabel('Hours')
                            ->addButtonLabel('Add Day')
                            ->columnSpanFull()
                            ->default([
                                'Monday' => '08:00 - 16:00',
                                'Tuesday' => '08:00 - 16:00',
                                'Wednesday' => '08:00 - 16:00',
                                'Thursday' => '08:00 - 16:00',
                                'Friday' => '08:00 - 16:00',
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('municipality.name')
                    ->label('Municipality')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('contact_info')
                    ->label('Contact')
                    ->searchable(),
                Tables\Columns\TextColumn::make('address')
                    ->limit(40)
                    ->tooltip(fn ($record) => $record->address),
                Tables\Columns\TextColumn::make('services_count')
                    ->counts('services')
                    ->label('Services')
                    ->badge()
                    ->color('success'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('municipality')
                    ->relationship('municipality', 'name')
                    ->searchable()
                    ->preload()
                    ->label('Filter by Municipality'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No offices yet')
            ->emptyStateDescription('Create your first government office.')
            ->emptyStateIcon('heroicon-o-building-office-2');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOffices::route('/'),
            'create' => Pages\CreateOffice::route('/create'),
            'edit' => Pages\EditOffice::route('/{record}/edit'),
        ];
    }
}
