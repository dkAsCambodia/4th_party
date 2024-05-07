<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class Agent extends Model implements Auditable
{
    use HasFactory, SoftDeletes;
    protected $guarded = ['id'];
    use \OwenIt\Auditing\Auditable;
    public function userData()
    {
        return $this->hasOne(User::class);
    }

    public function paymentAccount()
    {
        return $this->hasMany(PaymentAccount::class, 'id', 'agent_id');
    }

    public function defaultPaymentAccount()
    {
        return $this->hasOne(PaymentAccount::class)->where('default', 'yes');
    }
}
