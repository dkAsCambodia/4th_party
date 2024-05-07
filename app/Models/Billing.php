<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Billing extends Model implements Auditable
{
    use HasFactory;

    protected $guarded = ['id'];
    use \OwenIt\Auditing\Auditable;
    protected $casts =[
        'week_allow_withdrawals' => 'array'
    ];
}
