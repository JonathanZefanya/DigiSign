<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class ResetMonthlyQuotas extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'quotas:reset-monthly';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset monthly document quotas for all users';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Resetting monthly document quotas...');

        $count = User::query()->update(['documents_count_current_month' => 0]);

        $this->info("✓ Reset complete! {$count} users updated.");
        
        return Command::SUCCESS;
    }
}
