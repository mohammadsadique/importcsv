<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommonFeeCollection extends Model
{
    use HasFactory;

    protected $fillable = [
        'branchID','academic_year','voucher_type','roll_number','amount','transaction_dt','inactive','created_at','updated_at'
    ];
}
