<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\User;
use App\Models\LeaveBalance;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Leave type 6 = Holiday Compensation
     * Unit 2 = Uganda, 3 = Tanzania, 4 = Zambia, 5 = Zimbabwe
     * Allocates 1 day for all except Zambia which gets 2 days
     */
    public function up(): void
    {
        $countries = [
            2 => 1, // Uganda
            3 => 1, // Tanzania
            4 => 2, // Zambia
            5 => 1, // Zimbabwe
        ];

        foreach ($countries as $unitId => $days) {
            $employees = User::where('unit_id', $unitId)
                ->where('is_enabled', 1)
                ->whereNull('deleted_at')
                ->where('super_admin', 0)
                ->get();

            foreach ($employees as $user) {
                $balance = LeaveBalance::where('user_id', $user->id)
                    ->where('leave_type_id', 6)
                    ->first();

                if ($balance) {
                    // Add requested days to existing balance and allocated
                    $balance->allocated += $days;
                    $balance->balance   += $days;
                    $balance->save();
                } else {
                    // Create fresh record
                    LeaveBalance::create([
                        'user_id'       => $user->id,
                        'leave_type_id' => 6,
                        'allocated'     => $days,
                        'balance'       => $days,
                        'taken'         => 0,
                    ]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $countries = [
            2 => 1, // Uganda
            3 => 1, // Tanzania
            4 => 2, // Zambia
            5 => 1, // Zimbabwe
        ];

        foreach ($countries as $unitId => $days) {
            $employees = User::where('unit_id', $unitId)
                ->where('is_enabled', 1)
                ->whereNull('deleted_at')
                ->where('super_admin', 0)
                ->get();

            foreach ($employees as $user) {
                $balance = LeaveBalance::where('user_id', $user->id)
                    ->where('leave_type_id', 6)
                    ->first();

                if ($balance) {
                    $balance->allocated = max(0, $balance->allocated - $days);
                    $balance->balance   = max(0, $balance->balance - $days);
                    $balance->save();
                }
            }
        }
    }
};
