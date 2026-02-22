<?php

namespace App\Filament\Pages;

use App\Models\Company;
use App\Models\FollowUp;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    public function mount(): void
    {
        $this->updateExpiredFollowUps();
        $this->updatePendingFollowUps();
    }

    protected function updateExpiredFollowUps(): void
    {
        $today = now()->startOfDay();
        
        $expiredCompanies = Company::whereNotNull('next_followup_date')
            ->where('next_followup_date', '<', $today)
            ->whereNotIn('status', ['Lost', 'Won'])
            ->get();
        
        foreach ($expiredCompanies as $company) {
            $existingFollowUp = FollowUp::where('company_id', $company->id)
                ->where('followup_date', $company->next_followup_date)
                ->first();
            
            if (!$existingFollowUp) {
                FollowUp::create([
                    'company_id' => $company->id,
                    'user_id' => $company->user_id,
                    'followup_date' => $company->next_followup_date,
                    'status' => 'missed',
                    'notes' => 'Automatically marked as missed due to expired follow-up date',
                ]);
            } else {
                $existingFollowUp->update(['status' => 'missed']);
            }
            
            $company->update(['status' => 'Lost']);
        }
    }

    protected function updatePendingFollowUps(): void
    {
        $today = now()->startOfDay();
        
        $pendingFollowUps = FollowUp::where('status', 'pending')
            ->where('followup_date', '<', $today)
            ->get();
        
        foreach ($pendingFollowUps as $followUp) {
            $followUp->update(['status' => 'missed']);
            
            if ($followUp->company && !in_array($followUp->company->status, ['Lost', 'Won'])) {
                $followUp->company->update(['status' => 'Lost']);
            }
        }
    }
}
