<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Identify regular employees (Designation 5: Intern, 9: Attachee are excluded)
        \App\Models\LeaveBalance::where('leave_type_id', 1)
            ->whereHas('user', function ($query) {
                $query->whereNotIn('designation_id', [5, 9]);
            })
            ->chunk(100, function ($leaveBalances) {
                foreach ($leaveBalances as $leaveBalance) {
                    $leaveBalance->allocated = 5.25;
                    $leaveBalance->balance = 5.25 - $leaveBalance->taken;
                    $leaveBalance->save();
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No safe automated down fix for data
    }
};
