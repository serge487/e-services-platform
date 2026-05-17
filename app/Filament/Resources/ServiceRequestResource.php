<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServiceRequestResource\Pages;
use App\Models\ServiceRequest;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ServiceRequestResource extends Resource
{
    protected static ?string $model = ServiceRequest::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Service Management';
    protected static ?int $navigationSort = 2;

    // 🔒 Admin only monitors — no create/edit
    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Request Details')
                    ->schema([
                        Forms\Components\Select::make('citizen_id')
                            ->relationship('citizen', 'name')
                            ->searchable()
                            ->preload()
                            ->label('Citizen')
                            ->disabled(),
                        Forms\Components\Select::make('service_id')
                            ->relationship('service', 'name')
                            ->searchable()
                            ->preload()
                            ->label('Service')
                            ->disabled(),
                        Forms\Components\Select::make('status')
                            ->options([
                                'Pending'           => 'Pending',
                                'In Review'         => 'In Review',
                                'Missing Documents' => 'Missing Documents',
                                'Approved'          => 'Approved',
                                'Rejected'          => 'Rejected',
                                'Completed'         => 'Completed',
                            ])
                            ->disabled(),
                        Forms\Components\Textarea::make('office_notes')
                            ->label('Office Notes')
                            ->disabled()
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('Request #')
                    ->sortable(),
                Tables\Columns\TextColumn::make('citizen.name')
                    ->label('Citizen')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('service.name')
                    ->label('Service')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('service.office.name')
                    ->label('Office')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info'),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'gray'    => 'Pending',
                        'warning' => 'In Review',
                        'danger'  => fn ($state) => in_array($state, ['Missing Documents', 'Rejected']),
                        'success' => fn ($state) => in_array($state, ['Approved', 'Completed']),
                    ]),
                Tables\Columns\TextColumn::make('payment.status')
                    ->label('Payment')
                    ->badge()
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'completed',
                        'danger'  => 'failed',
                    ])
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Submitted')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'Pending'           => 'Pending',
                        'In Review'         => 'In Review',
                        'Missing Documents' => 'Missing Documents',
                        'Approved'          => 'Approved',
                        'Rejected'          => 'Rejected',
                        'Completed'         => 'Completed',
                    ]),
                Tables\Filters\SelectFilter::make('office')
                    ->relationship('service.office', 'name')
                    ->label('Filter by Office')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('No requests yet')
            ->emptyStateDescription('Service requests submitted by citizens will appear here.')
            ->emptyStateIcon('heroicon-o-document-text');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListServiceRequests::route('/'),
            'view'  => Pages\ViewServiceRequest::route('/{record}'),
        ];
    }
}