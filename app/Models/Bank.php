<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class Bank extends Model implements Auditable
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];
    use \OwenIt\Auditing\Auditable;
}
