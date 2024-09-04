<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusinessConnection extends Model
{
    use HasFactory;
    protected $table = "business_connection";

    protected $fillable = [
        'requesting_business_id',
        'receiving_business_id',
        'connection_status',
    ];
}
