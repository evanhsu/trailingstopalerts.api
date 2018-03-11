<?php

namespace App\Domain;

use Illuminate\Database\Eloquent\Model;

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

    public function users() {
        return $this->belongsToMany(User::class, 'stop_alerts', 'symbol', 'user_id');
    }
}
