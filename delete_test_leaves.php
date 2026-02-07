<?php
/**
 * Delete Test Leave Requests
 * 
 * This script deletes all Pending and Cancelled leave requests for user Steve (ID 18).
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Leave;
use App\Models\LeaveBalance;
use Illuminate\Support\Facades\Log;

echo "Fetching Pending and Cancelled leave requests for user 18...\n";
$leavesToDelete = Leave::where('user_id', 18)
    ->whereIn('status', ['Pending', 'Cancelled'])
    ->orderBy('id', 'desc')
    ->get();

if ($leavesToDelete->isEmpty()) {
    echo "No leave requests found to delete.\n";
    exit(0);
}

echo "Found " . $leavesToDelete->count() . " leave request(s) to delete:\n";
foreach ($leavesToDelete as $leave) {
    echo "  - ID {$leave->id}: {$leave->from} to {$leave->to} ({$leave->days} days) - {$leave->status}\n";
}

echo "\nRestoring leave balances...\n";
foreach ($leavesToDelete as $leave) {
    // Restore the leave balance if it was deducted
    // (Usually deducted on creation, so we should restore)
    $leaveBalance = LeaveBalance::where('user_id', $leave->user_id)
        ->where('leave_type_id', $leave->leave_type_id)
        ->first();
    
    if ($leaveBalance) {
        $leaveBalance->increment('balance', $leave->days);
        $leaveBalance->decrement('taken', $leave->days);
        echo "  ✓ Restored {$leave->days} days for User {$leave->user_id}\n";
    }
}

echo "\nDeleting leave requests...\n";
foreach ($leavesToDelete as $leave) {
    $leave->delete();
    echo "  ✓ Deleted Leave ID {$leave->id}\n";
}

echo "\n✅ Successfully deleted " . $leavesToDelete->count() . " leave request(s).\n";
