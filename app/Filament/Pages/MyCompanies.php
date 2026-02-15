<?php

namespace App\Filament\Pages;

use App\Filament\Resources\CompanyResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class MyCompanies extends ListRecords
{
    protected static string $resource = CompanyResource::class;

    protected static ?string $navigationLabel = 'My Companies';

    protected static ?string $navigationIcon = 'heroicon-o-user-circle';

    protected static ?int $navigationSort = 2;

    public static function canAccess(array $parameters = []): bool
    {
        return Auth::user()?->hasPermission('view_company') ?? true;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getTableQuery(): Builder
    {
        $query = CompanyResource::getEloquentQuery();
        $query->withoutGlobalScopes();
        return $query->where('user_id', Auth::id());
    }
}
