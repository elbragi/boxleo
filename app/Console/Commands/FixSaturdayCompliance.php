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
    protected $signature = 'fix:saturday-compliance {--date= : The date to fix (Y-m-d)}';

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
        $date = $this->option('date') ?: Carbon::today()->toDateString();
        $this->info("Fixing records for $date...");
        Log::info("Running FixSaturdayCompliance for $date");

        $attendances = Attendance::whereDate('attendance_date', $date)
                                 ->with(['user.unit'])
                                 ->get();

        $count = 0;
        foreach ($attendances as $attendance) {
            $user = $attendance->user;
            if (!$user || !$user->unit) continue;

            $clockIn = $attendance->clock_in_time;
            if (!$clockIn) continue;
            
            // Re-evaluate using the service (which now has the 08:31 logic)
            // Combined with a hard override for Saturdays to be safe
            $dayOfWeek = Carbon::parse($date)->dayOfWeek;
            
            $isLate = $service->isLate($date . ' ' . $clockIn, $user->unit);
            
            // Safety: Force 8:31 for Saturdays even if service has issues
            if ($dayOfWeek === Carbon::SATURDAY) {
                $isLate = Carbon::parse($clockIn)->greaterThan(Carbon::parse('08:31:00'));
            }

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
