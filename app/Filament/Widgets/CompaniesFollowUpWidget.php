<?php

namespace App\Filament\Widgets;

use App\Models\Company;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class CompaniesFollowUpWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Company::query()
                    ->whereNotNull('next_followup_date')
                    ->orderBy('next_followup_date', 'asc')
            )
            ->columns([
                Tables\Columns\TextColumn::make('company_name')
                    ->label('Company Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('contact_person')
                    ->label('Contact Person')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('contact_email')
                    ->label('Email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('contact_mobile')
                    ->label('Mobile')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'New' => 'info',
                        'Contacted' => 'warning',
                        'Meeting' => 'primary',
                        'Negotiation' => 'warning',
                        'Won' => 'success',
                        'Lost' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('next_followup_date')
                    ->label('Follow Up Date')
                    ->date()
                    ->sortable()
                    ->color(fn ($record) => $record->next_followup_date < now() ? 'danger' : 'warning'),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Assigned To')
                    ->sortable(),
            ])
            ->defaultSort('next_followup_date', 'asc');
    }
}
