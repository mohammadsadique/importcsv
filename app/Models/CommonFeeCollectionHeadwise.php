<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommonFeeCollectionHeadwise extends Model
{
    use HasFactory;

    protected $fillable = [
        'branchID','headID','commonFeeCollectionID','academic_year','voucher_type','amount','receipt_number','transaction_dt','created_at','updated_at'
    ];
}
