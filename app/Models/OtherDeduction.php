<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OtherDeduction extends Model
{
    use HasFactory;



    protected $fillable = [
        'payslip_id',
        'title',
        'label',
        'amount',
        'comment',
    ];

    public function payslip()
    {
        return $this->belongsTo(Payslip::class);
    }
}
