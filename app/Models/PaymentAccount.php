<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class PaymentAccount extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasFactory, SoftDeletes;
    protected $table = 'payment_accounts';
    protected $guarded = ['id'];
    protected $fillable = [
        'bank_name',
        'account_name',
        'account_type',
        'account_number',
        'account_province',
        'account_outlet',
        'account_city',
        'merchant_id',
        'agent_id',
        'remark',
        'default',
        'status',
        'bank_id',
    ];

    public function merchant()
    {
        return $this->belongsTo(Merchant::class);
    }

    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }

    public function bank()
    {
        return $this->belongsTo(Bank::class);
    }
}
