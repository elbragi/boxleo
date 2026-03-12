<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class AllocateLeaveDays extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:allocate-leave-days {mode?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates all annual leave balances to 3.5 days (initial run) and can be used for manual allocation';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $mode = $this->argument('mode') ?: 'incremental';

        if ($mode === 'reset') {
            $this->info('Resetting annual leave allocation to 3.5 days...');
            \App\Models\LeaveBalance::where('leave_type_id', 1)->chunk(100, function ($leaveBalances) {
                foreach ($leaveBalances as $leaveBalance) {
                    $leaveBalance->allocated = 3.5;
                    $leaveBalance->balance = 3.5 - $leaveBalance->taken;
                    $leaveBalance->save();
                    $this->info("Reset Annual Leave for user_id: {$leaveBalance->user_id}. New Balance: {$leaveBalance->balance}");
                }
            });
        } else {
            $this->info('Starting annual leave allocation: adding 1.75 days...');
            \App\Models\LeaveBalance::where('leave_type_id', 1)->chunk(100, function ($leaveBalances) {
                foreach ($leaveBalances as $leaveBalance) {
                    $leaveBalance->increment('allocated', 1.75);
                    $leaveBalance->increment('balance', 1.75);
                    $this->info("Updated Annual Leave for user_id: {$leaveBalance->user_id}. New Balance: {$leaveBalance->balance}");
                }
            });
        }

        $this->info('Annual leave allocation completed.');
    }
}
