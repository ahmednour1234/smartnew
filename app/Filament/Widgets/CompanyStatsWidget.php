<?php

namespace App\Filament\Widgets;

use App\Models\Company;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CompanyStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalCompanies = Company::count();
        $newCompanies = Company::where('status', 'New')->count();
        $wonCompanies = Company::where('status', 'Won')->count();
        $lostCompanies = Company::where('status', 'Lost')->count();

        return [
            Stat::make('Total Companies', $totalCompanies)
                ->description('All companies')
                ->descriptionIcon('heroicon-o-building-office')
                ->color('primary'),
            Stat::make('New Companies', $newCompanies)
                ->description('New status')
                ->descriptionIcon('heroicon-o-plus-circle')
                ->color('info'),
            Stat::make('Won Companies', $wonCompanies)
                ->description('Won deals')
                ->descriptionIcon('heroicon-o-check-circle')
                ->color('success'),
            Stat::make('Lost Companies', $lostCompanies)
                ->description('Lost deals')
                ->descriptionIcon('heroicon-o-x-circle')
                ->color('danger'),
        ];
    }
}
