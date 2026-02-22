<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\FollowUp;
use Illuminate\Console\Command;

class CheckExpiredFollowUps extends Command
{
    protected $signature = 'companies:check-expired-followups';
    
    protected $description = 'Check and mark companies with expired follow-up dates as Lost';

    public function handle()
    {
        $today = now()->startOfDay();
        
        $expiredCompanies = Company::whereNotNull('next_followup_date')
            ->where('next_followup_date', '<', $today)
            ->whereNotIn('status', ['Lost', 'Won'])
            ->get();
        
        $count = 0;
        
        foreach ($expiredCompanies as $company) {
            $company->update(['status' => 'Lost']);
            
            FollowUp::create([
                'company_id' => $company->id,
                'user_id' => $company->user_id,
                'followup_date' => $company->next_followup_date,
                'status' => 'missed',
                'notes' => 'Automatically marked as missed due to expired follow-up date',
            ]);
            
            $count++;
        }
        
        $this->info("Marked {$count} companies as Lost and created missed follow-up records.");
        
        return Command::SUCCESS;
    }
}
