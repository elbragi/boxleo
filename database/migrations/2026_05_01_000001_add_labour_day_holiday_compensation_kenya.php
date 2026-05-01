<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\User;
use App\Models\LeaveBalance;

return new class extends Migration
{
    // Leave type 6 = Holiday Compensation
    // Unit 1 = Kenya
    // Allocates 1 day for May 1st Labour Day to all active Kenya employees

    public function up(): void
    {
        $kenyaEmployees = User::where('unit_id', 1)
            ->where('is_enabled', 1)
            ->whereNull('deleted_at')
            ->where('super_admin', 0)
            ->get();

        foreach ($kenyaEmployees as $user) {
            $balance = LeaveBalance::where('user_id', $user->id)
                ->where('leave_type_id', 6)
                ->first();

            if ($balance) {
                // Add 1 day to existing balance and allocated
                $balance->allocated += 1;
                $balance->balance   += 1;
                $balance->save();
            } else {
                // Create fresh record
                LeaveBalance::create([
                    'user_id'       => $user->id,
                    'leave_type_id' => 6,
                    'allocated'     => 1,
                    'balance'       => 1,
                    'taken'         => 0,
                ]);
            }
        }
    }

    public function down(): void
    {
        // Reverse: subtract the 1 day (but never go below 0)
        $kenyaEmployees = User::where('unit_id', 1)
            ->where('is_enabled', 1)
            ->whereNull('deleted_at')
            ->where('super_admin', 0)
            ->get();

        foreach ($kenyaEmployees as $user) {
            $balance = LeaveBalance::where('user_id', $user->id)
                ->where('leave_type_id', 6)
                ->first();

            if ($balance) {
                $balance->allocated = max(0, $balance->allocated - 1);
                $balance->balance   = max(0, $balance->balance - 1);
                $balance->save();
            }
        }
    }
};
