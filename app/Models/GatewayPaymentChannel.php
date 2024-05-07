<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GatewayPaymentChannel extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

   /*  protected $fillable = [
        'channel_id',
        'channel_description',
        'gateway_account_id',
        'gateway_account_method_id',
        'payment_method',
        'daily_max_limit',
        'max_limit_per_trans',
        'daily_max_trans',
        'risk_control',
        'status',
        'created_at',
        'updated_at',
    ]; */
}
