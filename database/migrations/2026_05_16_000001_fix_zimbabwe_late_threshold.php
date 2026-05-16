<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    // Zimbabwe's late_threshold was 08:01 (too early) and weekend_threshold was 13:00
    // (their clock-out time, not their late-arrival cutoff).
    // Their shift starts at 08:30, so late = after 08:30 on both weekdays and Saturday.
    public function up(): void
    {
        DB::table('units')
            ->where('name', 'Zimbabwe')
            ->update([
                'late_threshold'    => '08:31:00',
                'weekend_threshold' => '08:31:00',
            ]);
    }

    public function down(): void
    {
        DB::table('units')
            ->where('name', 'Zimbabwe')
            ->update([
                'late_threshold'    => '08:01:00',
                'weekend_threshold' => '13:00:00',
            ]);
    }
};
