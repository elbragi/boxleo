<?php

namespace App\Http\Controllers;

use App\Models\Payslip;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PayrollApiController extends Controller
{



    // // fetch user payslip with user info

    // public function payslipWithUser($id)
    // {
    //     return Payslip::with('user')->findOrFail($id);
    // }   


    
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Payslip::with(['user', 'earnings', 'otherDeductions'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    // /**
    //  * Show the form for creating a new resource.
    //  */
    // public function create()
    // {
    //     //
    // }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'is_rider' => 'nullable|boolean',
            'rider_name' => 'nullable|string',
            'deliveries_count' => 'nullable|integer',
            'rate_per_delivery' => 'nullable|numeric',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'basic_pay' => 'required|numeric',
            'gross_pay' => 'required|numeric',
            'total_deductions' => 'required|numeric',
            'net_pay' => 'required|numeric',
            'month' => 'required|integer',
            'year' => 'required|integer',
            'payment_mode' => 'nullable|string',
            'bank' => 'nullable|string',
            'bank_branch' => 'nullable|string',
            'bank_account' => 'nullable|string',
            'pay_date' => 'nullable|date',
            'earnings' => 'nullable|array',
            'deductions' => 'nullable|array',
        ]);

        $payslip = Payslip::create($validatedData);

        // Save earnings
        if ($request->has('earnings')) {
            foreach ($request->earnings as $earning) {
                $payslip->earnings()->create([
                    'label' => $earning['label'] ?? $earning['earning_type']['label'] ?? 'Earning',
                    'amount' => $earning['amount'],
                ]);
            }
        }

        // Save other deductions
        if ($request->has('deductions')) {
            foreach ($request->deductions as $deduction) {
                $payslip->otherDeductions()->create([
                    'label' => $deduction['label'] ?? $deduction['deduction_type']['label'] ?? 'Deduction',
                    'amount' => $deduction['amount'],
                    'comment' => $deduction['comment'] ?? null,
                ]);
            }
        }

        return response()->json([
            'message' => 'Payroll generated and saved successfully',
            'payroll' => $payslip->load('user', 'earnings', 'otherDeductions'),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
        return Payslip::with('user')->findOrFail($id);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //


        Payslip::destroy($id);
        return response()->json(['message' => 'Payslip deleted']);
    }


 public function generatePayslip(Request $request)
    {


        Log::info('Generating payslip for user: ' . $request->all());
        // Logic to generate payslip
        // This is a placeholder, implement your logic here
        $userId = $request->input('user_id');
        $month = $request->input('month');
        $year = $request->input('year');

        // Example logic to create a payslip
        $payslip = Payslip::create([
            'user_id' => $userId,
            'month' => $month,
            'year' => $year,
            'amount' => 0, // Calculate the amount based on earnings and deductions
        ]);

        return response()->json($payslip);
    }
}
