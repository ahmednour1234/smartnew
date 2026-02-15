<?php

namespace App\Filament\Pages;

use App\Filament\Resources\CompanyResource;
use App\Models\Company;
use Filament\Actions;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class MyCompanies extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationLabel = 'My Companies';

    protected static ?string $navigationIcon = 'heroicon-o-user-circle';

    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.pages.my-companies';

    public static function canAccess(array $parameters = []): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        $user = Auth::user();
        $canViewBooked = $user?->hasPermission('view_booked_companies') ?? false;

        return $table
            ->query(Company::query()->where('user_id', Auth::id()))
            ->modifyQueryUsing(function (Builder $query) {
                $query->withoutGlobalScopes([
                    SoftDeletingScope::class,
                ]);
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
            ])
            ->filters([
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
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->url(fn ($record) => CompanyResource::getUrl('view', ['record' => $record])),
                Tables\Actions\EditAction::make()
                    ->url(fn ($record) => CompanyResource::getUrl('edit', ['record' => $record])),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                ]),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->url(CompanyResource::getUrl('create')),
            ]);
    }
}
