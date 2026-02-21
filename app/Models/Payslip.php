<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payslip extends Model
{
    use SoftDeletes;

    protected $fillable = [
        "user_id",
        "is_rider",
        "rider_name",
        "deliveries_count",
        "rate_per_delivery",
        "start_date",
        "end_date",
        "basic_pay",
        "gross_pay",
        "total_deductions",
        "net_pay",
        "month",
        "year",
        "pay_date",
        "payment_mode",
        "bank",
        "bank_branch",
        "bank_account",
    ];



    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function earnings()
    {
        return $this->hasMany(Earning::class);
    }

    public function statutoryDeductions()
    {
        return $this->hasOne(StatutoryDeduction::class);
    }

    public function otherDeductions()
    {
        return $this->hasMany(OtherDeduction::class);
    }
}
