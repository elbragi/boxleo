<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "--- BEVERLY USER ---" . PHP_EOL;
$beverly = \App\Models\User::where('firstname', 'LIKE', '%Beverly%')->where('lastname', 'LIKE', '%Awinja%')->first();
if ($beverly) {
    echo $beverly->toJson(JSON_PRETTY_PRINT) . PHP_EOL;
} else {
    echo "Beverly not found!" . PHP_EOL;
}

echo "--- DESIGNATIONS ---" . PHP_EOL;
echo \App\Models\Designation::all()->toJson(JSON_PRETTY_PRINT) . PHP_EOL;

echo "--- DEPARTMENTS ---" . PHP_EOL;
echo \App\Models\Department::all()->toJson(JSON_PRETTY_PRINT) . PHP_EOL;
