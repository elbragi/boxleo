<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

DB::beginTransaction();

try {
    $allan = User::where('firstname', 'Allan')->where('lastname', 'Bolim')->first();
    if (!$allan) {
        throw new Exception("Allan Bolim not found");
    }

    $itDept = DB::table('departments')->where('name', 'IT')->first();
    if (!$itDept) {
        throw new Exception("IT department not found");
    }

    // 1. Update designation to Manager (1)
    $allan->designation_id = 1;
    $allan->save();
    echo "Updated Allan Bolim's designation to Manager.\n";

    // 2. Update manager_departments
    // Check if there's an existing entry for IT dept
    $existing = DB::table('manager_departments')->where('department_id', $itDept->id)->first();
    
    if ($existing) {
        DB::table('manager_departments')
            ->where('id', $existing->id)
            ->update([
                'user_id' => $allan->id,
                'updated_at' => now()
            ]);
        echo "Updated existing manager entry for IT department to Allan Bolim.\n";
    } else {
        DB::table('manager_departments')->insert([
            'user_id' => $allan->id,
            'department_id' => $itDept->id,
            'created_at' => now(),
            'updated_at' => now()
        ]);
        echo "Created new manager entry for IT department for Allan Bolim.\n";
    }

    DB::commit();
    echo "Successfully assigned Allan Bolim as IT Manager.\n";
} catch (Exception $e) {
    DB::rollBack();
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
