<?php

namespace App\Filament\Widgets;

use App\Models\Company;
use App\Models\Event;
use App\Models\FollowUp;
use App\Models\Meeting;
use App\Models\Package;
use App\Models\User;
use App\Filament\Resources\FollowUpResource;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Str;

class StatsOverviewWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalFollowUps = FollowUp::count();
        $missedFollowUps = FollowUp::where('status', 'missed')->count();
        $pendingFollowUps = FollowUp::where('status', 'pending')->count();

        $followUpBaseUrl = $this->getFollowUpUrl();

        return [
            Stat::make('Total Users', User::count())
                ->description('Registered users')
                ->descriptionIcon('heroicon-o-users')
                ->color('success'),
            Stat::make('Total Companies', Company::count())
                ->description('Active companies')
                ->descriptionIcon('heroicon-o-building-office')
                ->color('primary'),
            Stat::make('Total Follow Ups', $totalFollowUps)
                ->description('All follow ups')
                ->descriptionIcon('heroicon-o-clock')
                ->color('info')
                ->url($followUpBaseUrl),
            Stat::make('Missed Follow Ups', $missedFollowUps)
                ->description('Expired follow ups')
                ->descriptionIcon('heroicon-o-exclamation-triangle')
                ->color('danger')
                ->url($followUpBaseUrl . '?tableFilters[status][value]=missed'),
            Stat::make('Pending Follow Ups', $pendingFollowUps)
                ->description('Upcoming follow ups')
                ->descriptionIcon('heroicon-o-clock')
                ->color('warning')
                ->url($followUpBaseUrl . '?tableFilters[status][value]=pending'),
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

    protected function getFollowUpUrl(): string
    {
        $url = FollowUpResource::getUrl('index');
        if (Str::startsWith($url, 'http')) {
            $parsed = parse_url($url);
            $path = '/public' . ($parsed['path'] ?? '');
            return ($parsed['scheme'] ?? 'https') . '://' . ($parsed['host'] ?? '') . $path;
        }
        return '/public' . $url;
    }
}
