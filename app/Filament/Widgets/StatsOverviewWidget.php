<?php

namespace App\Filament\Widgets;

use App\Models\Company;
use App\Models\Event;
use App\Models\Meeting;
use App\Models\Package;
use App\Models\User;
use App\Filament\Resources\CompanyResource;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Users', User::count())
                ->description('Registered users')
                ->descriptionIcon('heroicon-o-users')
                ->color('success'),
            Stat::make('Total Companies', Company::count())
                ->description('Active companies')
                ->descriptionIcon('heroicon-o-building-office')
                ->color('primary'),
            Stat::make('Follow Ups', Company::whereNotNull('next_followup_date')->count())
                ->description('Companies with follow ups')
                ->descriptionIcon('heroicon-o-clock')
                ->color('warning')
                ->url(CompanyResource::getUrl('index') . '?tableFilters[next_followup_date][value]=1'),
            Stat::make('Total Meetings', Meeting::count())
                ->description('Scheduled meetings')
                ->descriptionIcon('heroicon-o-calendar-days')
                ->color('info'),
            Stat::make('Total Events', Event::count())
                ->description('Active events')
                ->descriptionIcon('heroicon-o-calendar')
                ->color('warning'),
            Stat::make('Total Packages', Package::count())
                ->description('Available packages')
                ->descriptionIcon('heroicon-o-cube')
                ->color('success'),
        ];
    }
}
