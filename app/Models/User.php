<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'profile_image',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function business_user()
    {
        return $this->hasMany(BusinessUser::class, "user_id");
    }

    public function businesses()
    {
        $businessUser = $this->with('business_user.business')->where('id', $this->id)->first();
        if (!$businessUser || !$businessUser->business_user) {
            return collect();
        }
        return $businessUser->business_user->map(function ($x) {
            return $x->business ?? null;
        })->filter();
    }

    public function unSubscribedBusinesses()
    {
        $unsubscribed = [];
        $businesses = $this->businesses();
        if ($businesses->isEmpty()) {
            return $unsubscribed;
        }
        foreach ($businesses as $business) {
            if ($business && !$business->subscribed('default')) {
                $unsubscribed[] = $business;
            }
        }
        return $unsubscribed;
    }

    public function unSubscribedBusiness()
    {
        $unsubscribedBusinesses = $this->unSubscribedBusinesses();
        return $unsubscribedBusinesses[0] ?? null;
    }
    public static function defaultBusiness()
    {
        $defaultBusiness = DefaultBusiness::where('user_id', Auth::id())->pluck('business_id')->first();

        if ($defaultBusiness) {
            return Business::find($defaultBusiness);
        }

        $businessUser = BusinessUser::where('user_id', Auth::id())->pluck('business_id')->first();
        return Business::find($businessUser);
    }
}
