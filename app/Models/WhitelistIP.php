<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use OwenIt\Auditing\Contracts\Auditable;

class WhitelistIP extends Model implements Auditable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;
    use \OwenIt\Auditing\Auditable;
    protected $table = 'whitelist_ips';
    protected $guarded = ['id'];
    protected $fillable = [
        'address',
        'remarks',
        'status',
    ];

    public function toArray()
    {
        $attributes = parent::toArray();
        $attributes['status'] == 1 ? $attributes['status_string'] = 'Enabled' : $attributes['status_string'] = 'Disabled';
        return $attributes;
    }
}
