<?php
namespace App\Domain;
/**
 * App\Domain\Stock
 *
 * @property string $symbol
 * @property string|null $name
 * @property float $price
 * @property \Carbon\Carbon $quote_updated_at
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Domain\StopAlert[] $stopAlerts
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Domain\User[] $users
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Domain\Stock whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Domain\Stock whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Domain\Stock wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Domain\Stock whereQuoteUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Domain\Stock whereSymbol($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Domain\Stock whereUpdatedAt($value)
 */
class Stock extends \Eloquent
{
    protected $table = 'stocks';
    protected $primaryKey = 'symbol';
    protected $keyType = 'string';
    public $incrementing = false;
    protected $dates = [
        'created_at',
        'updated_at',
        'quote_updated_at',
    ];

    protected $fillable = [
        'symbol',
        'name',
        'price',
        'quote_updated_at'
    ];

    /**
     * @var bool $FIRE_EVENTS   This value should be manually checked before firing any Model-based events
     */
    public static $FIRE_EVENTS = true;

    /**
     * @param bool $FIRE_EVENTS
     */
    public static function setFIREEVENTS($FIRE_EVENTS)
    {
        self::$FIRE_EVENTS = $FIRE_EVENTS;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function stopAlerts() {
        return $this->hasMany(StopAlert::class, 'symbol', 'symbol');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users() {
        return $this->belongsToMany(User::class, 'stop_alerts', 'symbol', 'user_id');
    }
}
