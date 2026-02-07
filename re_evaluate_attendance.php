<?php

use App\Models\Attendance;
use App\Models\User;
use App\Models\Holiday;
use Carbon\Carbon;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Threshold: 08:31 AM
$newThresholdStr = '08:31';
$today = '2026-02-07';

echo "Re-evaluating attendance for $today...\n";

$attendances = Attendance::where('attendance_date', $today)
    ->with('user.unit')
    ->get();

$updatedCount = 0;

foreach ($attendances as $attendance) {
    if (!$attendance->user || !$attendance->user->unit) continue;
    
    $unit = $attendance->user->unit;
    $clockInTime = $attendance->clock_in_time;
    
    if (!$clockInTime) continue;

    // Combine date and time
    $dateTimeToParse = $today . ' ' . $clockInTime;
    $userTime = Carbon::parse($dateTimeToParse);
    
    $userDayOfWeek = $userTime->dayOfWeek;
    
    $weekendDays = is_array($unit->weekend_day)
        ? $unit->weekend_day
        : [$unit->weekend_day ?? Carbon::SATURDAY];

    $isHoliday = Holiday::whereDate('date', $today)->exists();

    $lateThreshold = '';
    if ($userDayOfWeek === Carbon::SUNDAY) {
        $lateThreshold = '11:00';
    } elseif (in_array($userDayOfWeek, $weekendDays) || $isHoliday) {
        $lateThreshold = $newThresholdStr;
    } else {
        $lateThreshold = $unit->late_threshold ?? '08:01';
    }

    $thresholdTime = $userTime->copy()->setTimeFromTimeString($lateThreshold);
    $isLate = $userTime->greaterThan($thresholdTime);
    $newStatus = $isLate ? 'Late' : 'In Time';

    if ($attendance->status !== $newStatus) {
        echo "Updating User {$attendance->user->id} ({$attendance->user->firstname}): {$attendance->status} -> $newStatus (Time: $clockInTime)\n";
        $attendance->status = $newStatus;
        $attendance->save();
        $updatedCount++;
    }
}

echo "Finished. Updated $updatedCount records.\n";
