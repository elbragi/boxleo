<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    // Cap annual leave allocated at 7 days and recalculate balance = allocated - taken.
    public function up(): void
    {
        // First cap allocated to 7 for anyone over the limit
        DB::statement("
            UPDATE leave_balances
            SET allocated = 7
            WHERE leave_type_id = 1
              AND allocated > 7
              AND deleted_at IS NULL
        ");

        // Recalculate balance for all annual leave records
        DB::statement("
            UPDATE leave_balances
            SET balance = allocated - taken
            WHERE leave_type_id = 1
              AND deleted_at IS NULL
        ");
    }

    public function down(): void {}
};
