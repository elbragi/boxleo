<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Attendance;
use App\Services\AttendanceService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class FixSaturdayCompliance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:saturday-compliance';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Re-evaluate today\'s attendance status based on new 08:31 threshold';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(AttendanceService $service)
    {
        $today = Carbon::today()->toDateString();
        $this->info("Fixing records for $today...");
        Log::info("Running FixSaturdayCompliance for $today");

        $attendances = Attendance::whereDate('attendance_date', $today)
                                 ->with(['user.unit'])
                                 ->get();

        $count = 0;
        foreach ($attendances as $attendance) {
            $user = $attendance->user;
            if (!$user || !$user->unit) continue;

            $clockIn = $attendance->clock_in_time;
            if (!$clockIn) continue;
            
            // Re-evaluate using the service (which now has the 08:31 logic)
            // Note: Service expects $clockInTime. If it's H:i:s, Service uses Carbon::parse() which uses today's date.
            // Since we are only fixing *today's* records, this is acceptable.
            
            $isLate = $service->isLate($clockIn, $user->unit);
            $newStatus = $isLate ? 'Late' : 'In Time';

            if ($attendance->status !== $newStatus) {
                $this->info("Updating User {$user->name} (ID: {$user->id}): {$attendance->status} -> {$newStatus} (Clock In: $clockIn)");
                $attendance->status = $newStatus;
                $attendance->save();
                $count++;
            }
        }
        
        $this->info("Done. Updated $count records.");
        return 0;
    }
}
