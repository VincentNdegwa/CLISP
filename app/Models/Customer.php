<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $table = 'customers';

    protected $fillable = [
        'full_names',
        'email',
        'phone_number',
        'address',
        'business_id'
    ];

    public function business()
    {
        return $this->belongsTo(Business::class, "business_id", "business_id");
    }
}
