<?php
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Leave;
use App\Models\User;

$userId = 18;

// 1. Check for duplicate users
$users = User::where('firstname', 'Steven')->orWhere('lastname', 'Ligawa')->get();
echo "Found " . $users->count() . " users matching Steven Ligawa:\n";
foreach ($users as $u) {
    echo " - ID {$u->id}: {$u->firstname} {$u->lastname} ({$u->email})\n";
}

// 2. Find leaves created today (including soft deleted)
echo "\nSearching for leaves created today for User {$userId}...\n";
$leaves = Leave::withTrashed()
    ->where('user_id', $userId)
    ->whereDate('created_at', '>=', '2026-02-06')
    ->get();

echo "Found " . $leaves->count() . " leaves.\n";

foreach ($leaves as $leave) {
    echo " - ID {$leave->id} ({$leave->status}): Created {$leave->created_at}, Deleted: {$leave->deleted_at}\n";
    $leave->forceDelete();
    echo "   -> Force Deleted.\n";
}

echo "\nDone.\n";
