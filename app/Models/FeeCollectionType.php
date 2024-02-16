<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeeCollectionType extends Model
{
    use HasFactory;

    protected $fillable = [
        'branchID','collectionhead','created_at','updated_at'
    ];
}
