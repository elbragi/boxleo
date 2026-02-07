<?php
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\LeaveBalance;
use Illuminate\Support\Facades\Log;

$userId = 18;
$leaveTypeId = 1; // Annual Leave
$daysToRevert = 18;

echo "Correcting leave balance for User {$userId}...\n";

$balance = LeaveBalance::where('user_id', $userId)
    ->where('leave_type_id', $leaveTypeId)
    ->first();

if (!$balance) {
    echo "Leave balance not found!\n";
    exit(1);
}

echo "Current Balance: {$balance->balance}\n";
echo "Current Taken: {$balance->taken}\n";
echo "Reverting {$daysToRevert} days (incorrectly restored from Cancelled leaves)...\n";

$balance->decrement('balance', $daysToRevert);
$balance->increment('taken', $daysToRevert);

$balance->refresh();
echo "New Balance: {$balance->balance}\n";
echo "New Taken: {$balance->taken}\n";
echo "✅ Correction complete.\n";
