<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "--- BEVERLY CHECK ---" . PHP_EOL;
$user = \App\Models\User::find(9);
if ($user) {
    echo "Email: " . $user->email . PHP_EOL;
    echo "Designation ID: " . $user->designation_id . PHP_EOL;
    echo "Department ID: " . $user->department_id . PHP_EOL;
    echo "Is Enabled: " . ($user->is_enabled ? 'YES' : 'NO') . PHP_EOL;
    echo "Deleted At: " . ($user->deleted_at ?: 'NULL') . PHP_EOL;
    
    // Simulate API index
    $users = \App\Models\User::all();
    $foundInAll = $users->contains('id', 9);
    echo "Found in User::all(): " . ($foundInAll ? 'YES' : 'NO') . PHP_EOL;
} else {
    echo "User 9 NOT FOUND" . PHP_EOL;
}

echo "--- DESIGNATION 16 CHECK ---" . PHP_EOL;
$d = \App\Models\Designation::find(16);
echo "Designation 16 Name: " . ($d ? $d->name : 'NOT FOUND') . PHP_EOL;

echo "--- BUILD STRINGS CHECK ---" . PHP_EOL;
$assets = glob('public/build/assets/app-*.js');
foreach($assets as $asset) {
    $content = file_get_contents($asset);
    if (strpos($content, 'designation_id===16') !== false || strpos($content, 'designation_id === 16') !== false || strpos($content, '16') !== false) {
        echo "Found potentially relevant code in: " . basename($asset) . PHP_EOL;
        // Search for the specific pattern from Departments.vue
        if (preg_match('/designation_id[=!]==16/', $content, $matches)) {
            echo "MATCH: " . $matches[0] . PHP_EOL;
        }
    }
}
