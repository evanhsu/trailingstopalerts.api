<?php
namespace App\Domain;
/**
 * App\Domain\StopAlert
 *
 * @property int $id
 * @property string $symbol
 * @property int $user_id
 * @property float $trail_amount
 * @property string $trail_amount_units
 * @property float $high_price
 * @property \Carbon\Carbon $high_price_updated_at
 * @property float $trigger_price
 * @property int $triggered
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \App\Domain\Stock $stock
 * @property-read \App\Domain\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Domain\StopAlert whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Domain\StopAlert whereHighPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Domain\StopAlert whereHighPriceUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Domain\StopAlert whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Domain\StopAlert whereSymbol($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Domain\StopAlert whereTrailAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Domain\StopAlert whereTrailAmountUnits($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Domain\StopAlert whereTriggerPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Domain\StopAlert whereTriggered($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Domain\StopAlert whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Domain\StopAlert whereUserId($value)
 */
class StopAlert extends \Eloquent
{
    /**
     * @var array
     */
    protected $fillable = [
        'symbol',
        'user_id',
        'trail_amount',
        'trail_amount_units',
        'high_price',
        'high_price_updated_at',
        'trigger_price',
        'triggered'
    ];

    /**
     * @var array
     */
    protected $dates = [
        'created_at',
        'updated_at',
        'high_price_updated_at',
    ];

    /**
     * @param float $highPrice
     * @param float $trailAmount
     * @param string $trailAmountUnits
     * @return bool|float
     */
    public static function calculateTriggerPrice($highPrice, $trailAmount, $trailAmountUnits = 'percent') {
        switch($trailAmountUnits) {
            case 'percent':
                return $highPrice * (100 - $trailAmount);
                break;
            case 'dollars':
                return $highPrice - $trailAmount;
                break;
            default:
                return false;
        }
    }

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

    /**
     * @return $this
     */
    public function updateTriggerPrice() {
        $oldTriggerPrice = $this->trigger_price;
        $newTriggerPrice = self::calculateTriggerPrice($this->high_price, $this->trail_amount, $this->trail_amount_units);

        if($oldTriggerPrice !== $newTriggerPrice) {
            $this->triggerPrice = $newTriggerPrice;
            $this->save();
        }

        return $this;
    }
}
