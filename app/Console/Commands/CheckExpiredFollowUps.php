<?php

namespace App\Console\Commands;

use App\Models\Company;
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
            $count++;
        }
        
        $this->info("Marked {$count} companies as Lost due to expired follow-up dates.");
        
        return Command::SUCCESS;
    }
}
