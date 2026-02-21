<?php

use App\Models\LeaveType;
use App\Models\LeaveBalance;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $leaveType = LeaveType::updateOrCreate(
            ['name' => 'Rest Day'],
            ['days' => 1, 'comment' => 'One day a month']
        );

        Log::info('Rest Day LeaveType ensures: ' . $leaveType->id);

        // Ensure all users have a balance record for this new type
        $users = User::whereNull('deleted_at')->get();
        foreach ($users as $user) {
            LeaveBalance::firstOrCreate(
                ['user_id' => $user->id, 'leave_type_id' => $leaveType->id],
                ['allocated' => 1, 'taken' => 0, 'balance' => 1]
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $leaveType = LeaveType::where('name', 'Rest Day')->first();
        if ($leaveType) {
            LeaveBalance::where('leave_type_id', $leaveType->id)->delete();
            $leaveType->delete();
        }
    }
};
