<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class MyMember extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    protected $fillable = [
        'amount',
        'customer_id',
        'merchant_id',
        'call_back_url',
        'status',
        'transaction_id',
        'payment_method'
    ];

    use HasFactory;
}
