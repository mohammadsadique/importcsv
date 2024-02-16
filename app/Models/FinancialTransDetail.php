<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinancialTransDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'branchID','headID','financialTransID','academic_year','voucher_type','amount','transaction_dt','created_at','updated_at'
    ];
}
