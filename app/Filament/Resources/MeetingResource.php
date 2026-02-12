<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MeetingResource\Pages;
use App\Filament\Resources\MeetingResource\RelationManagers;
use App\Models\Company;
use App\Models\Meeting;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class MeetingResource extends Resource
{
    protected static ?string $model = Meeting::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    public static function canViewAny(): bool
    {
        return Auth::user()?->hasPermission('view_any_meetings') ?? false;
    }

    public static function canCreate(): bool
    {
        return Auth::user()?->hasPermission('create_meetings') ?? false;
    }

    public static function canEdit($record): bool
    {
        return Auth::user()?->hasPermission('update_meetings') ?? false;
    }

    public static function canDelete($record): bool
    {
        return Auth::user()?->hasPermission('delete_meetings') ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('User')
                            ->relationship('user', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->native(false),
                        Forms\Components\Select::make('company_id')
                            ->label('Company')
                            ->relationship('company', 'company_name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->native(false),
                    ])
                    ->columns(2),
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\DateTimePicker::make('meeting_at')
                            ->label('Meeting at')
                            ->required()
                            ->native(false)
                            ->displayFormat('m/d/Y H:i')
                            ->seconds(false),
                        Forms\Components\TextInput::make('duration_minutes')
                            ->label('Duration minutes')
                            ->required()
                            ->numeric()
                            ->default(0)
                            ->minValue(0),
                    ])
                    ->columns(2),
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\Select::make('type')
                            ->label('Type')
                            ->options([
                                'In-person' => 'In-person',
                                'Virtual' => 'Virtual',
                                'Phone' => 'Phone',
                                'Video Call' => 'Video Call',
                            ])
                            ->required()
                            ->default('In-person')
                            ->native(false),
                        Forms\Components\Select::make('outcome')
                            ->label('Outcome')
                            ->options([
                                'Successful' => 'Successful',
                                'Follow-up needed' => 'Follow-up needed',
                                'Cancelled' => 'Cancelled',
                                'No show' => 'No show',
                            ])
                            ->native(false),
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
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('company.company_name')
                    ->label('Company')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('meeting_at')
                    ->label('Meeting at')
                    ->dateTime('m/d/Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('duration_minutes')
                    ->label('Duration (min)')
                    ->sortable()
                    ->suffix(' min'),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'In-person' => 'success',
                        'Virtual' => 'info',
                        'Phone' => 'warning',
                        'Video Call' => 'primary',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('outcome')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'Successful' => 'success',
                        'Follow-up needed' => 'warning',
                        'Cancelled' => 'danger',
                        'No show' => 'gray',
                        default => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('user_id')
                    ->label('User')
                    ->relationship('user', 'name'),
                Tables\Filters\SelectFilter::make('company_id')
                    ->label('Company')
                    ->relationship('company', 'company_name'),
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'In-person' => 'In-person',
                        'Virtual' => 'Virtual',
                        'Phone' => 'Phone',
                        'Video Call' => 'Video Call',
                    ]),
                Tables\Filters\SelectFilter::make('outcome')
                    ->options([
                        'Successful' => 'Successful',
                        'Follow-up needed' => 'Follow-up needed',
                        'Cancelled' => 'Cancelled',
                        'No show' => 'No show',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListMeetings::route('/'),
            'create' => Pages\CreateMeeting::route('/create'),
            'edit' => Pages\EditMeeting::route('/{record}/edit'),
        ];
    }
}
