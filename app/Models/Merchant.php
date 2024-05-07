<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;


class Merchant extends Model implements Auditable
{
    use HasFactory, SoftDeletes;
    use \OwenIt\Auditing\Auditable;
    protected $guarded = ['id'];

    public function userData()
    {
        return $this->hasOne(User::class);
    }

    public function paymentAccount()
    {
        return $this->hasMany(PaymentAccount::class, 'id', 'merchant_id');
    }

    public function agent()
    {
        return $this->hasOne(Agent::class, 'id', 'agent_id');
    }

    public function defaultPaymentAccount()
    {
        return $this->hasOne(PaymentAccount::class)->where('default', 'yes');
    }
}
