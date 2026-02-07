<?php

use Illuminate\Http\Request;
use App\Http\Controllers\Api\RequisitionApiController;
use App\Models\Requisition;
use Carbon\Carbon;

require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Create dummy requisitions
$req1 = Requisition::create([
    'user_id' => 1, 'department_id' => 1, 'status' => 'Pending', 'approver_type' => 'HR',
    'created_at' => Carbon::parse('2025-01-01')
]);
$req1->items()->create(['name' => 'Stationery', 'quantity' => 1, 'unit_cost' => 100, 'total_cost' => 100, 'description' => 'Pens']);

$req2 = Requisition::create([
    'user_id' => 1, 'department_id' => 1, 'status' => 'Pending', 'approver_type' => 'HR',
    'created_at' => Carbon::parse('2025-01-05')
]);
$req2->items()->create(['name' => 'Airtime', 'quantity' => 1, 'unit_cost' => 50, 'total_cost' => 50, 'description' => 'Call data']);

echo "Created Req 1 (Stationery, Jan 1) ID: " . $req1->id . "\n";
echo "Created Req 2 (Airtime, Jan 5) ID: " . $req2->id . "\n\n";

$controller = new RequisitionApiController();

// TEST 1: Filter by Item Name (Account)
echo "Test 1: Filter by Item Name 'Stationery'\n";
$request1 = Request::create('/api/v1/filter-requisitions', 'POST', [
    'item_names' => ['Stationery']
]);
$response1 = $controller->filter($request1);
$data1 = $response1->getData();
echo "Found " . count($data1->requisitions) . " requisitions.\n";
if (count($data1->requisitions) > 0) {
    echo "First ID: " . $data1->requisitions[0]->id . "\n";
}

// TEST 2: Filter by Date Range
echo("\nTest 2: Filter by Date (Jan 4 - Jan 6)\n");
$request2 = Request::create('/api/v1/filter-requisitions', 'POST', [
    'date_created_start' => '2025-01-04',
    'date_created_end' => '2025-01-06'
]);
$response2 = $controller->filter($request2);
$data2 = $response2->getData();
echo "Found " . count($data2->requisitions) . " requisitions.\n";
if (count($data2->requisitions) > 0) {
    echo "First ID: " . $data2->requisitions[0]->id . "\n";
}

// Cleanup
$req1->forceDelete();
$req2->forceDelete();
echo "\nCleaned up.\n";
