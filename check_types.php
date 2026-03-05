<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$beverly = \App\Models\User::where('firstname', 'LIKE', '%Beverly%')->first();
$benson = \App\Models\User::where('firstname', 'LIKE', '%Benson%')->first();

echo "BEVERLY: " . ($beverly ? $beverly->firstname . " (ID: " . $beverly->id . ", Desig: " . $beverly->designation_id . " [" . gettype($beverly->designation_id) . "])" : "Not found") . PHP_EOL;
echo "BENSON: " . ($benson ? $benson->firstname . " (ID: " . $benson->id . ", Desig: " . $benson->designation_id . " [" . gettype($benson->designation_id) . "])" : "Not found") . PHP_EOL;

echo "--- DESIGNATIONS ---" . PHP_EOL;
foreach(\App\Models\Designation::all() as $d) {
    echo $d->id . ": " . $d->name . PHP_EOL;
}
