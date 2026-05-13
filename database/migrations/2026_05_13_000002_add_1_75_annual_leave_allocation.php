<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add 1.75 days to annual leave (leave_type_id=1) for all employees
        // excluding Interns (designation_id=5) and Attachees (designation_id=9).
        // Recalculate balance as allocated - taken to ensure accuracy.
        DB::statement("
            UPDATE leave_balances lb
            INNER JOIN users u ON u.id = lb.user_id
            SET lb.allocated = lb.allocated + 1.75,
                lb.balance   = (lb.allocated + 1.75) - lb.taken
            WHERE lb.leave_type_id = 1
              AND lb.deleted_at IS NULL
              AND u.deleted_at IS NULL
              AND u.designation_id NOT IN (5, 9)
        ");
    }

    public function down(): void
    {
        DB::statement("
            UPDATE leave_balances lb
            INNER JOIN users u ON u.id = lb.user_id
            SET lb.allocated = lb.allocated - 1.75,
                lb.balance   = (lb.allocated - 1.75) - lb.taken
            WHERE lb.leave_type_id = 1
              AND lb.deleted_at IS NULL
              AND u.deleted_at IS NULL
              AND u.designation_id NOT IN (5, 9)
        ");
    }
};
