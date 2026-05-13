<?php

namespace App\Console;

use App\Jobs\AddMonthlyLeaveBalance;
use App\Models\Leave;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;


class Kernel extends ConsoleKernel
{

    protected function schedule(Schedule $schedule)
    {
        // Log::info("Schedule is running...");

        $schedule->call(function () {
            Leave::where('to', '<', now())->update(['is_active' => false]);
        })->daily();



         $schedule->command('leave:reminder')->dailyAt('07:30');
         $schedule->command('leave:reminder')->dailyAt('16:30')
            ->before(function () {
                Log::info("leave:reminder command is about to run...");
            })
            ->after(function () {
                Log::info("leave:reminder command has executed.");
            });

            // Birthday wishes
            $schedule->command('app:send-birthday-notification')->dailyAt('08:00')
            ->before(function () {
                Log::info("🎉 app:send-birthday-notification is about to run...");
            })
            ->after(function () {
                Log::info("🎂 app:send-birthday-notification finished running.");
            });


            $schedule->command('app:allocate-leave-days')
            ->monthlyOn(1, '00:00')
            ->onSuccess(function () {
                Log::info("✅ app:allocate-leave-days executed successfully on " . now()->toDateTimeString());
            })
            ->onFailure(function (\Throwable $exception) {
                Log::error("❌ Failed to execute app:allocate-leave-days on " . now()->toDateTimeString() . ": " . $exception->getMessage());
            });


            // $schedule->job(new \App\Jobs\DailyAttendanceJob)->everyTwoMinutes();

            $schedule->job(new \App\Jobs\DailyAttendanceJob)->dailyAt('9:59');

            // Study reminders — runs daily at 08:00; command checks which day it is
            $schedule->command('study:send-reminders')->dailyAt('08:00');



    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
