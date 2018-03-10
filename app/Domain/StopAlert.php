<?php
namespace App\Domain;

use Illuminate\Database\Eloquent\Model;

class StopAlert extends Model
{
    protected $fillable = [
        'symbol',
        'user_id',
        'trail_amount',
        'trail_amount_units',
        'high_price',
        'high_price_updated_at',
        'triggered'
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'high_price_updated_at',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user() {
        return $this->belongsTo(User::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function stock() {
        return $this->belongsTo(Stock::class, 'symbol', 'symbol');
    }
}
