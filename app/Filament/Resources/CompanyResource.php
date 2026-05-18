<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CompanyResource\Pages;
use App\Filament\Resources\CompanyResource\RelationManagers;
use App\Models\Company;
use App\Models\Country;
use App\Models\Event;
use App\Models\Package;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class CompanyResource extends Resource
{
    protected static ?string $model = Company::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office';

    public static function canViewAny(): bool
    {
        return Auth::user()?->hasPermission('view_any_companies') ?? false;
    }

    public static function canCreate(): bool
    {
        return Auth::user()?->hasPermission('create_companies') ?? false;
    }

    public static function canEdit($record): bool
    {
        $user = Auth::user();
        if (!$user) {
            return false;
        }

        if (!$user->hasPermission('update_companies')) {
            return false;
        }

        if ($user->hasRole('admin')) {
            return true;
        }

        return $record->user_id === $user->id;
    }

    public static function canView($record): bool
    {
        $user = Auth::user();
        if (!$user) {
            return false;
        }

        if ($user->hasRole('admin')) {
            return true;
        }

        return $record->user_id === $user->id;
    }

    public static function canDelete($record): bool
    {
        $user = Auth::user();
        if (!$user) {
            return false;
        }

        if (!$user->hasPermission('delete_companies')) {
            return false;
        }

        if ($user->hasRole('admin')) {
            return true;
        }

        return $record->user_id === $user->id;
    }

    public static function form(Form $form): Form
    {
        $user = Auth::user();
        $isAdmin = $user?->hasRole('admin') ?? false;

        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'New' => 'New',
                                'Contacted' => 'Contacted',
                                'Meeting' => 'Meeting',
                                'Negotiation' => 'Negotiation',
                                'Won' => 'Won',
                                'Lost' => 'Lost',
                            ])
                            ->required()
                            ->default('New')
                            ->native(false),
                        Forms\Components\TextInput::make('company_name')
                            ->label('Company name')
                            ->required()
                            ->maxLength(255),
                    ])
                    ->columns(2),
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\Select::make('package_id')
                            ->label('Package')
                            ->relationship('package', 'name')
                            ->searchable()
                            ->preload()
                            ->native(false),
                        Forms\Components\Select::make('event_id')
                            ->label('Event')
                            ->relationship('event', 'name')
                            ->searchable()
                            ->preload()
                            ->native(false),
                    ])
                    ->columns(2),
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('contact_person')
                            ->label('Contact person')
                            ->maxLength(255),
                        Forms\Components\Select::make('country_id')
                            ->label('Country')
                            ->relationship('country', 'name')
                            ->searchable()
                            ->preload()
                            ->native(false),
                    ])
                    ->columns(2),
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('contact_email')
                            ->label('Contact email')
                            ->email()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('contact_mobile')
                            ->label('Contact mobile')
                            ->tel()
                            ->maxLength(255),
                    ])
                    ->columns(2),
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\DatePicker::make('next_followup_date')
                            ->label('Next followup date')
                            ->native(false)
                            ->displayFormat('m/d/Y'),
                        $isAdmin ? Forms\Components\Select::make('user_id')
                            ->label('Assigned User')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->native(false) : Forms\Components\Hidden::make('user_id')
                            ->default(fn () => Auth::id()),
                    ])
                    ->columns(2),
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\Textarea::make('notes')
                            ->label('Notes')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        $user = Auth::user();
        $isAdmin = $user?->hasRole('admin') ?? false;

        return $table
            ->columns([
                Tables\Columns\TextColumn::make('company_name')
                    ->label('Company name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'New' => 'gray',
                        'Contacted' => 'info',
                        'Meeting' => 'warning',
                        'Negotiation' => 'primary',
                        'Won' => 'success',
                        'Lost' => 'danger',
                        default => 'success',
                    })
                    ->formatStateUsing(function ($state, $record) use ($user, $isAdmin) {
                        if ($isAdmin) {
                            return $state;
                        }
                        if ($state === 'Won') {
                            return 'Booked';
                        }
                        if ($record->user_id === $user?->id) {
                            return $state;
                        }
                        return 'Booked';
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('contact_person')
                    ->label('Contact person')
                    ->searchable()
                    ->formatStateUsing(function ($state, $record) use ($user, $isAdmin) {
                        if ($isAdmin || $record->user_id === $user?->id) {
                            return $state;
                        }
                        return '—';
                    }),
                Tables\Columns\TextColumn::make('contact_email')
                    ->label('Contact email')
                    ->searchable()
                    ->formatStateUsing(function ($state, $record) use ($user, $isAdmin) {
                        if ($isAdmin || $record->user_id === $user?->id) {
                            return $state;
                        }
                        return '—';
                    }),
                Tables\Columns\TextColumn::make('package.name')
                    ->label('Package')
                    ->sortable(),
                Tables\Columns\TextColumn::make('event.name')
                    ->label('Event')
                    ->sortable(),
                Tables\Columns\TextColumn::make('country.name')
                    ->label('Country')
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Assigned User')
                    ->sortable()
                    ->formatStateUsing(function ($state, $record) use ($user, $isAdmin) {
                        if ($isAdmin || $record->user_id === $user?->id) {
                            return $state;
                        }
                        return '—';
                    })
                    ->toggleable(isToggledHiddenByDefault: !$isAdmin),
                Tables\Columns\TextColumn::make('next_followup_date')
                    ->label('Next Followup')
                    ->date('m/d/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters(array_filter([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'New' => 'New',
                        'Contacted' => 'Contacted',
                        'Meeting' => 'Meeting',
                        'Negotiation' => 'Negotiation',
                        'Won' => 'Won',
                        'Lost' => 'Lost',
                    ]),
                Tables\Filters\SelectFilter::make('package_id')
                    ->label('Package')
                    ->relationship('package', 'name'),
                Tables\Filters\SelectFilter::make('event_id')
                    ->label('Event')
                    ->relationship('event', 'name'),
                Tables\Filters\SelectFilter::make('country_id')
                    ->label('Country')
                    ->relationship('country', 'name'),
                $isAdmin ? Tables\Filters\SelectFilter::make('user_id')
                    ->label('Assigned User')
                    ->relationship('user', 'name') : null,
            ]))
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->visible(fn ($record) => static::canView($record)),
                Tables\Actions\EditAction::make()
                    ->visible(fn ($record) => static::canEdit($record)),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn () => Auth::user()?->hasPermission('delete_companies') ?? false),
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
            'index' => Pages\ListCompanies::route('/'),
            'create' => Pages\CreateCompany::route('/create'),
            'view' => Pages\ViewCompany::route('/{record}'),
            'edit' => Pages\EditCompany::route('/{record}/edit'),
        ];
    }
}
