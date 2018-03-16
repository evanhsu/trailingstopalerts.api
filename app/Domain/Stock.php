<?php

namespace App\Domain;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Domain\Stock
 *
 * @property string $symbol
 * @property string|null $name
 * @property float $open
 * @property float $close
 * @property float $high
 * @property float $low
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
class Stock extends Model
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
        'open',
        'close',
        'high',
        'low',
        'quote_updated_at',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function stopAlerts()
    {
        return $this->hasMany(StopAlert::class, 'symbol', 'symbol');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'stop_alerts', 'symbol', 'user_id');
    }

    public function setOpenAttribute($value)
    {
        $this->attributes['open'] = (float)$value;
    }

    public function setCloseAttribute($value)
    {
        $this->attributes['close'] = (float)$value;
    }

    public function setHighAttribute($value)
    {
        $this->attributes['high'] = (float)$value;
    }

    public function setLowAttribute($value)
    {
        $this->attributes['low'] = (float)$value;
    }
}

