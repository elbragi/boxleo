<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$payslip = App\Models\Payslip::first();
if ($payslip) {
    if (!$payslip->is_rider) {
        $payslip->load(['user' => function($q) { $q->withTrashed(); }]);
    }
    $employee = $payslip->user;
    $name = $payslip->is_rider ? $payslip->rider_name : ($employee ? $employee->firstname : 'Employee');
    $pdf = \PDF::loadView('payroll.payslip', compact('payslip', 'employee'))->setPaper('a5', 'portrait');
    $filename = 'payslip-test.pdf';
    $response = $pdf->download($filename);
    echo "Content-Disposition: " . $response->headers->get('Content-Disposition') . "\n";
} else {
    echo "NO PAYSLIPS FOUND";
}
