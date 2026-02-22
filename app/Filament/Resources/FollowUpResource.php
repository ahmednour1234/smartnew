<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FollowUpResource\Pages;
use App\Models\FollowUp;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class FollowUpResource extends Resource
{
    protected static ?string $model = FollowUp::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static ?string $navigationLabel = 'Follow Ups';

    protected static ?string $modelLabel = 'Follow Up';

    protected static ?string $pluralModelLabel = 'Follow Ups';

    protected static ?int $navigationSort = 3;

    public static function canViewAny(): bool
    {
        $user = Auth::user();
        if (!$user) {
            return false;
        }
        return $user->hasPermission('view_any_followups') || $user->hasPermission('view_followups');
    }

    public static function canView($record): bool
    {
        $user = Auth::user();
        if (!$user) {
            return false;
        }

        if ($user->hasPermission('view_any_followups')) {
            return true;
        }

        if ($user->hasPermission('view_followups')) {
            return $record->user_id === $user->id;
        }

        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('company_id')
                    ->label('Company')
                    ->relationship('company', 'company_name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->native(false),
                Forms\Components\DatePicker::make('followup_date')
                    ->label('Follow Up Date')
                    ->required()
                    ->native(false)
                    ->displayFormat('m/d/Y')
                    ->default(now()),
                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Pending',
                        'completed' => 'Completed',
                        'missed' => 'Missed',
                    ])
                    ->default('pending')
                    ->required()
                    ->native(false),
                Forms\Components\Textarea::make('notes')
                    ->label('Notes')
                    ->maxLength(65535)
                    ->columnSpanFull(),
                Forms\Components\Select::make('user_id')
                    ->label('Assigned To')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->default(fn () => Auth::id())
                    ->native(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        $user = Auth::user();
        $canViewAny = $user?->hasPermission('view_any_followups') ?? false;

        return $table
            ->modifyQueryUsing(function (Builder $query) use ($user, $canViewAny) {
                if ($canViewAny) {
                    return;
                }
                if ($user && $user->hasPermission('view_followups')) {
                    $query->where('user_id', $user->id);
                }
            })
            ->columns([
                Tables\Columns\TextColumn::make('company.company_name')
                    ->label('Company Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('followup_date')
                    ->label('Follow Up Date')
                    ->date('m/d/Y')
                    ->sortable()
                    ->color(fn ($record) => match($record->status) {
                        'missed' => 'danger',
                        'pending' => $record->followup_date < now() ? 'warning' : 'info',
                        'completed' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'completed' => 'success',
                        'missed' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Pending',
                        'completed' => 'Completed',
                        'missed' => 'Missed',
                        default => $state,
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Assigned To')
                    ->sortable(),
                Tables\Columns\TextColumn::make('notes')
                    ->label('Notes')
                    ->limit(50)
                    ->tooltip(fn ($record) => $record->notes)
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime('m/d/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'completed' => 'Completed',
                        'missed' => 'Missed',
                    ])
                    ->native(false),
                Tables\Filters\SelectFilter::make('company_id')
                    ->label('Company')
                    ->relationship('company', 'company_name')
                    ->searchable()
                    ->preload()
                    ->native(false),
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
            ->defaultSort('followup_date', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFollowUps::route('/'),
            'create' => Pages\CreateFollowUp::route('/create'),
            'edit' => Pages\EditFollowUp::route('/{record}/edit'),
        ];
    }
}
