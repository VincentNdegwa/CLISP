<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusinessUser extends Model
{
    use HasFactory;
    protected $table = "business_users";
    protected $fillable = [
        'business_id',
        'user_id'
    ];

    public function business()
    {
        return $this->belongsTo(Business::class, 'business_id', 'business_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, "user_id");
    }
}
