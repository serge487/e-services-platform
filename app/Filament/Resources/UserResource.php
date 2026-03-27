<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\Office;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'User Management';

    protected static ?int $navigationSort = 1;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereIn('role', ['municipality', 'office_staff', 'citizen']);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Account Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\TextInput::make('phone_number')
                            ->tel()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\TextInput::make('password')
                            ->password()
                            ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->maxLength(255)
                            ->label(fn (string $operation) => $operation === 'edit' ? 'New Password (leave blank to keep)' : 'Password'),
                    ])->columns(2),

                Forms\Components\Section::make('Role & Access')
                    ->schema([
                        Forms\Components\Select::make('role')
                            ->options([
                                'municipality' => 'Municipality User (full access)',
                                'office_staff' => 'Office staff (desk: requests, appointments, chat)',
                                'citizen' => 'Citizen',
                            ])
                            ->required()
                            ->live()
                            ->label('Role'),
                        Forms\Components\Select::make('municipality_id')
                            ->relationship('municipality', 'name')
                            ->searchable()
                            ->preload()
                            ->label('Municipality')
                            ->helperText(fn (Get $get) => $get('role') === 'office_staff'
                                ? 'Each municipality has one government office; desk staff are assigned to that office automatically.'
                                : null)
                            ->visible(fn (Get $get) => in_array($get('role'), ['municipality', 'office_staff'], true))
                            ->required(fn (Get $get) => in_array($get('role'), ['municipality', 'office_staff'], true))
                            ->live()
                            ->rules([
                                fn (Get $get): \Closure => function (string $attribute, $value, \Closure $fail) use ($get): void {
                                    if ($get('role') !== 'office_staff' || blank($value)) {
                                        return;
                                    }
                                    if (! Office::query()->where('municipality_id', $value)->exists()) {
                                        $fail('This municipality has no office yet. Create it under Offices first (one per municipality).');
                                    }
                                },
                            ]),
                        Forms\Components\Placeholder::make('resolved_office')
                            ->label('Government office')
                            ->content(function (Get $get): string {
                                if ($get('role') !== 'office_staff') {
                                    return '';
                                }
                                $municipalityId = $get('municipality_id');
                                if (blank($municipalityId)) {
                                    return 'Select a municipality.';
                                }
                                $name = Office::query()->where('municipality_id', $municipalityId)->value('name');

                                return $name
                                    ? (string) $name
                                    : 'No office on file — add the office for this municipality before creating desk staff.';
                            })
                            ->visible(fn (Get $get) => $get('role') === 'office_staff'),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Account Active')
                            ->default(true)
                            ->inline(false),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('phone_number')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\BadgeColumn::make('role')
                    ->colors([
                        'warning' => 'municipality',
                        'info' => 'office_staff',
                        'success' => 'citizen',
                    ]),
                Tables\Columns\TextColumn::make('municipality.name')
                    ->label('Municipality')
                    ->sortable()
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('office.name')
                    ->label('Office')
                    ->sortable()
                    ->placeholder('—')
                    ->toggleable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->options([
                        'municipality' => 'Municipality User',
                        'office_staff' => 'Office staff',
                        'citizen' => 'Citizen',
                    ]),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Account Status')
                    ->trueLabel('Active')
                    ->falseLabel('Inactive'),
                Tables\Filters\SelectFilter::make('municipality')
                    ->relationship('municipality', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('toggleActive')
                    ->label(fn (User $record) => $record->is_active ? 'Deactivate' : 'Activate')
                    ->icon(fn (User $record) => $record->is_active ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                    ->color(fn (User $record) => $record->is_active ? 'danger' : 'success')
                    ->requiresConfirmation()
                    ->action(fn (User $record) => $record->update(['is_active' => ! $record->is_active])),
                Tables\Actions\DeleteAction::make()

                    ->hidden(fn (User $record) => $record->id === Auth::id()),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No users yet')
            ->emptyStateDescription('Create your first municipality or citizen user.')
            ->emptyStateIcon('heroicon-o-users');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
