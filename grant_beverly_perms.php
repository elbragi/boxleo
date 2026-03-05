<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\User::find(9);
if ($user) {
    $user->givePermissionTo('view_team_leaves');
    echo "Permission granted successfully to " . $user->email . PHP_EOL;
} else {
    echo "User not found" . PHP_EOL;
}
