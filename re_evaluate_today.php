<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

use App\Models\Attendance;
use App\Models\User;
use App\Http\Controllers\Api\AttendanceApiController;
use Carbon\Carbon;

$controller = new AttendanceApiController();
$date = '2026-03-06';
$attendances = Attendance::whereDate('attendance_date', $date)->get();

echo "Re-evaluating " . $attendances->count() . " records for $date...\n";

// Reflection to access private isLate method
$reflection = new \ReflectionClass(AttendanceApiController::class);
$isLateMethod = $reflection->getMethod('isLate');
$isLateMethod->setAccessible(true);

$updatedCount = 0;
foreach ($attendances as $attendance) {
    if (!$attendance->clock_in_time || $attendance->clock_in_time === '00:00:00') continue;
    
    $user = User::with('unit')->find($attendance->user_id);
    if (!$user || !$user->unit) continue;
    
    $isLate = $isLateMethod->invokeArgs($controller, [$attendance->clock_in_time, $user->unit, $date]);
    $newStatus = $isLate ? 'Late' : 'In Time';
    
    if ($attendance->status !== $newStatus) {
        echo "Updating Attendance ID {$attendance->id} (User: {$user->firstname}): {$attendance->status} -> $newStatus (Clock In: {$attendance->clock_in_time})\n";
        $attendance->status = $newStatus;
        $attendance->save();
        $updatedCount++;
    }
}

echo "Re-evaluation complete. Updated $updatedCount records.\n";
