<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServiceResource\Pages;
use App\Models\Service;
use App\Models\Office;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ServiceResource extends Resource
{
    protected static ?string $model = Service::class;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationGroup = 'Service Management';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Service Information')
                    ->schema([
                        Forms\Components\Select::make('office_id')
                            ->relationship('office', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->live()
                            ->label('Office'),
                        Forms\Components\Select::make('category_id')
                            ->relationship('category', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->label('Category'),
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g. Birth Certificate'),
                        Forms\Components\Textarea::make('description')
                            ->rows(3)
                            ->columnSpanFull()
                            ->placeholder('Brief description of the service...'),
                    ])->columns(2),

                Forms\Components\Section::make('Pricing & Duration')
                    ->schema([
                        Forms\Components\TextInput::make('price')
                            ->required()
                            ->numeric()
                            ->prefix('$')
                            ->minValue(0),
                        Forms\Components\TextInput::make('duration_days')
                            ->required()
                            ->numeric()
                            ->suffix('days')
                            ->minValue(1)
                            ->label('Processing Duration'),
                    ])->columns(2),

                Forms\Components\Section::make('Required Documents')
                    ->schema([
                        Forms\Components\TagsInput::make('required_documents')
                            ->placeholder('Add a document and press Enter')
                            ->columnSpanFull()
                            ->label('Required Documents'),
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
                Tables\Columns\TextColumn::make('office.name')
                    ->label('Office')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Category')
                    ->searchable()
                    ->badge()
                    ->color('warning'),
                Tables\Columns\TextColumn::make('price')
                    ->money('USD')
                    ->sortable(),
                Tables\Columns\TextColumn::make('duration_days')
                    ->label('Duration')
                    ->suffix(' days')
                    ->sortable(),
                Tables\Columns\TextColumn::make('service_requests_count')
                    ->counts('serviceRequests')
                    ->label('Requests')
                    ->badge()
                    ->color('success'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('office')
                    ->relationship('office', 'name')
                    ->searchable()
                    ->preload()
                    ->label('Filter by Office'),
                Tables\Filters\SelectFilter::make('category')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload()
                    ->label('Filter by Category'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No services yet')
            ->emptyStateDescription('Services are created by municipality users.')
            ->emptyStateIcon('heroicon-o-clipboard-document-list');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListServices::route('/'),
            'create' => Pages\CreateService::route('/create'),
            'edit'   => Pages\EditService::route('/{record}/edit'),
        ];
    }
}