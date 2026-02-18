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
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;

class CompanyResource extends Resource
{
    protected static ?string $model = Company::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office';

    public static function canViewAny(): bool
    {
        $user = Auth::user();
        if (!$user) {
            return false;
        }
        return $user->hasPermission('view_any_companies') || $user->hasPermission('view_company');
    }

    public static function canView($record): bool
    {
        $user = Auth::user();
        if (!$user) {
            return false;
        }

        if ($user->hasPermission('view_any_companies')) {
            return true;
        }

        if ($user->hasPermission('view_company')) {
            return $record->user_id === $user->id;
        }

        return false;
    }

    public static function canCreate(): bool
    {
       return false;
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
        $canViewAssignedUser = $user?->hasPermission('view_booked_companies') ?? false;

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
                        $canViewAssignedUser
                            ? Forms\Components\Select::make('user_id')
                                ->label('Assigned User')
                                ->relationship('user', 'name')
                                ->searchable()
                                ->preload()
                                ->native(false)
                                ->placeholder('Unassigned')
                                ->rules([
                                    function ($get, $livewire) {
                                        return function (string $attribute, $value, \Closure $fail) use ($get, $livewire) {
                                            if ($value) {
                                                $currentRecordId = $livewire->record->id ?? null;
                                                $count = Company::where('user_id', $value)
                                                    ->when($currentRecordId, fn ($query) => $query->where('id', '!=', $currentRecordId))
                                                    ->count();

                                                if ($count >= 60) {
                                                    $fail('This user already has the maximum of 60 companies assigned.');
                                                }
                                            }
                                        };
                                    },
                                ])
                            : Forms\Components\Group::make([
                                Forms\Components\TextInput::make('assigned_user_display')
                                    ->label('Assigned User')
                                    ->default('Booked')
                                    ->disabled()
                                    ->dehydrated(false),
                                Forms\Components\Hidden::make('user_id')
                                    ->default(fn () => Auth::id()),
                            ]),
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
        $canViewAny = $user?->hasPermission('view_any_companies') ?? false;
        $canViewCompany = $user?->hasPermission('view_company') ?? false;
        $canViewBooked = $user?->hasPermission('view_booked_companies') ?? false;

        return $table
            ->modifyQueryUsing(function (Builder $query) use ($user, $canViewAny, $canViewCompany) {
                $query->withoutGlobalScopes([
                    SoftDeletingScope::class,
                ]);
                if ($canViewAny) {
                    return;
                }
                if ($canViewCompany && $user) {
                    $query->where('user_id', $user->id);
                }
            })
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
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('contact_person')
                    ->label('Contact person')
                    ->searchable(),
                Tables\Columns\TextColumn::make('contact_email')
                    ->label('Contact email')
                    ->searchable(),
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
                    ->toggleable(isToggledHiddenByDefault: !$canViewBooked)
                    ->visible(fn () => $canViewBooked),
                Tables\Columns\TextColumn::make('next_followup_date')
                    ->label('Next Followup')
                    ->date('m/d/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters(array_filter([
                Tables\Filters\TrashedFilter::make(),
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
                $canViewBooked ? Tables\Filters\SelectFilter::make('user_id')
                    ->label('Assigned User')
                    ->relationship('user', 'name') : null,
                Tables\Filters\TernaryFilter::make('user_id')
                    ->label('Assignment Status')
                    ->placeholder('All companies')
                    ->trueLabel('Assigned')
                    ->falseLabel('Unassigned')
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('user_id'),
                        false: fn (Builder $query) => $query->whereNull('user_id'),
                    ),
                Tables\Filters\TernaryFilter::make('next_followup_date')
                    ->label('Follow Up')
                    ->placeholder('All companies')
                    ->trueLabel('Has Follow Up')
                    ->falseLabel('No Follow Up')
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('next_followup_date'),
                        false: fn (Builder $query) => $query->whereNull('next_followup_date'),
                    ),
            ]))
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->visible(fn ($record) => static::canView($record)),
                Tables\Actions\EditAction::make()
                    ->visible(fn ($record) => static::canEdit($record)),
                Tables\Actions\Action::make('assign_to_me')
                    ->label('Assign to me')
                    ->icon('heroicon-o-user-plus')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(function ($record) {
                        $user = Auth::user();
                        if (!$user) return false;
                        if ($record->user_id !== null) return false;

                        $userCount = Company::where('user_id', $user->id)->count();
                        return $userCount < 60;
                    })
                    ->action(function ($record) {
                        $user = Auth::user();
                        if (!$user) return;

                        $userCount = Company::where('user_id', $user->id)->count();
                        if ($userCount >= 60) {
                            Notification::make()
                                ->danger()
                                ->title('Cannot assign')
                                ->body('You have reached the maximum of 60 companies.')
                                ->send();
                            return;
                        }

                        $record->update(['user_id' => $user->id]);
                        Notification::make()
                            ->success()
                            ->title('Company assigned')
                            ->body('The company has been assigned to you.')
                            ->send();
                    }),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn ($record) => static::canDelete($record)),
                Tables\Actions\RestoreAction::make()
                    ->visible(fn ($record) => static::canDelete($record)),
                Tables\Actions\ForceDeleteAction::make()
                    ->visible(fn ($record) => static::canDelete($record)),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn () => Auth::user()?->hasPermission('delete_companies') ?? false),
                    Tables\Actions\RestoreBulkAction::make()
                        ->visible(fn () => Auth::user()?->hasPermission('delete_companies') ?? false),
                    Tables\Actions\ForceDeleteBulkAction::make()
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
