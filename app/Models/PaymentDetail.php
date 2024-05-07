<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class PaymentDetail extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    // protected $fillable = [
    //     'idd',
    //     'phone_number',
    //     'value',
    //     'type',
    //     'expire_at',
    // ];
    protected $guarded = ['id'];

    protected $table = 'payment_details';

    protected $casts = [
        'response_data' => 'json',
    ];

    public function paymentMaps()
    {
        return $this->hasOne(PaymentMap::class, 'id', 'product_id');
    }

    public function merchantData()
    {
        return $this->hasOne(Merchant::class, 'merchant_code', 'merchant_code')->with('agent');
    }
}
