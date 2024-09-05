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
        'requesting_user_id',
        'receiving_business_id',
        'receiving_user_id',
        'connection_status',
    ];



    public function businessRequester()
    {
        return $this->belongsTo(Business::class, 'requesting_business_id', 'business_id');
    }

    public function businessReceiver()
    {
        return $this->belongsTo(Business::class, 'receiving_business_id', 'business_id');
    }

    public function userRequester(){
        return $this->belongsTo(User::class, 'requesting_user_id');
    }

    public function userReceiver(){
        return $this->belongsTo(User::class, 'receiving_user_id');
    }
}
