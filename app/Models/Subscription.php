<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Subscription extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'subscriptions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'subscription_start',
        'subscription_end',
        'payment_status',
        'amount_paid',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'subscription_start' => 'datetime',
        'subscription_end' => 'datetime',
        'amount_paid' => 'decimal:2',
    ];

    /**
     * Get the remaining days of the subscription.
     *
     * @return int
     */
    public function getRemainingDaysAttribute()
    {
        return Carbon::now()->diffInDays($this->subscription_end, false);
    }

    /**
     * Scope a query to only include active subscriptions.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('subscription_end', '>', Carbon::now());
    }

    /**
     * Scope a query to only include subscriptions with a certain payment status.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $status
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePaymentStatus($query, $status)
    {
        return $query->where('payment_status', $status);
    }

    /**
     * Mark the subscription as paid.
     *
     * @param float $amount
     * @return void
     */
    public function markAsPaid($amount)
    {
        $this->update([
            'payment_status' => 'Paid',
            'amount_paid' => $amount,
        ]);
    }

    public function busniess()
    {
        return $this->hasOne(Business::class, "subscription_plan");
    }
}
