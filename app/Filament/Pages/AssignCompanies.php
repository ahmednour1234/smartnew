<?php

namespace App\Filament\Pages;

use App\Models\Company;
use App\Models\User;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AssignCompanies extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationLabel = 'Assign Companies';

    protected static ?string $navigationIcon = 'heroicon-o-user-plus';

    protected static ?int $navigationSort = 3;

    protected static string $view = 'filament.pages.assign-companies';

    public static function canAccess(array $parameters = []): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(User::query())
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('User')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('assigned_count')
                    ->label('Assigned Companies')
                    ->counts('companies')
                    ->sortable(),
                Tables\Columns\TextColumn::make('unassigned_count')
                    ->label('Unassigned Companies')
                    ->getStateUsing(function () {
                        return Company::whereNull('user_id')->count();
                    })
                    ->sortable(false),
            ])
            ->actions([
                Tables\Actions\Action::make('book')
                    ->label('Book')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Assign Unassigned Companies')
                    ->modalDescription(fn (User $record) => 'Assign unassigned companies to ' . $record->name . '?')
                    ->action(function (User $record) {
                        $unassignedCount = Company::whereNull('user_id')->count();
                        $assignedCount = $record->companies()->count();
                        $available = 60 - $assignedCount;
                        $toAssign = min($unassignedCount, $available);
                        
                        if ($toAssign <= 0) {
                            \Filament\Notifications\Notification::make()
                                ->danger()
                                ->title('Cannot assign')
                                ->body('This user has reached the maximum of 60 companies or there are no unassigned companies.')
                                ->send();
                            return;
                        }
                        
                        Company::whereNull('user_id')
                            ->limit($toAssign)
                            ->update(['user_id' => $record->id]);
                        
                        \Filament\Notifications\Notification::make()
                            ->success()
                            ->title('Companies assigned')
                            ->body("Assigned {$toAssign} companies to {$record->name}.")
                            ->send();
                    }),
            ])
            ->headerActions([
                Tables\Actions\Action::make('unassign_all')
                    ->label('Unassign All')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Unassign All Companies')
                    ->modalDescription('Are you sure you want to unassign all companies?')
                    ->action(function () {
                        $count = Company::whereNotNull('user_id')->count();
                        Company::whereNotNull('user_id')->update(['user_id' => null]);
                        
                        \Filament\Notifications\Notification::make()
                            ->success()
                            ->title('Companies unassigned')
                            ->body("Unassigned {$count} companies.")
                            ->send();
                    }),
            ]);
    }
}
