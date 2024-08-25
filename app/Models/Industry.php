<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Industry extends Model
{
    use HasFactory;
    protected $table = 'industries';
    protected $fillable = ['name'];
    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function businesses()
    {
        return $this->hasMany(Business::class, 'industry_id');
    }
}
