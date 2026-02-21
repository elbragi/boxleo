<?php

namespace App\Jobs;

use App\Models\LeaveBalance;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class AddMonthlyLeaveBalance implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('AddMonthlyLeaveBalance job started.');

        // 1. Update Annual Leave (ID 1)
        LeaveBalance::where('leave_type_id', 1)->chunk(100, function ($leaveBalances) {
            foreach ($leaveBalances as $leaveBalance) {
                $leaveBalance->increment('allocated', 1.75);
                $leaveBalance->increment('balance', 1.75);
                Log::info("Updated Annual Leave for user_id: {$leaveBalance->user_id}");
            }
        });

        // 2. Update Rest Day Leave Type
        $restDayType = \App\Models\LeaveType::where('name', 'Rest Day')->first();
        if ($restDayType) {
            LeaveBalance::where('leave_type_id', $restDayType->id)->chunk(100, function ($leaveBalances) {
                foreach ($leaveBalances as $leaveBalance) {
                    $leaveBalance->increment('allocated', 1);
                    $leaveBalance->increment('balance', 1);
                    Log::info("Updated Rest Day Leave for user_id: {$leaveBalance->user_id}");
                }
            });
        }

        Log::info('AddMonthlyLeaveBalance job completed.');
    }
}
