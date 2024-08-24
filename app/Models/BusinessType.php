<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusinessType extends Model
{
    use HasFactory;
    protected $table = 'business_types';
    protected $fillable = ['name'];

    // Define the relationship with Business model
    public function businesses()
    {
        return $this->hasMany(Business::class, 'business_type_id');
    }
}
