<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
class SettleRequestTrans extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;
    protected $guarded = ['id'];

    protected $table = 'settle_request_trans';

    public function paymentDetails()
    {
        return $this->hasOne(PaymentDetail::class, 'id', 'payment_detail_id')->with('paymentMaps');
    }
}
