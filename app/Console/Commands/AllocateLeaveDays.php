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
            $maxDays = 7;
            $this->info("Starting annual leave allocation: adding 1.75 days (cap: {$maxDays}, excluding Interns and Attachees)...");
            \App\Models\LeaveBalance::where('leave_type_id', 1)
                ->whereHas('user', function ($query) {
                    $query->whereNotIn('designation_id', [5, 9]); // 5: Intern, 9: Attachee
                })
                ->chunk(100, function ($leaveBalances) use ($maxDays) {
                    foreach ($leaveBalances as $leaveBalance) {
                        $newAllocated = min($leaveBalance->allocated + 1.75, $maxDays);
                        $leaveBalance->allocated = $newAllocated;
                        $leaveBalance->balance   = $newAllocated - $leaveBalance->taken;
                        $leaveBalance->save();
                        $this->info("Updated Annual Leave for user_id: {$leaveBalance->user_id}. Allocated: {$newAllocated}, Balance: {$leaveBalance->balance}");
                    }
                });
        }

        $this->info('Annual leave allocation completed.');
    }
}
