<?php

namespace App\Domain;

use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    protected $table = 'stocks';
    protected $primaryKey = 'symbol';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'symbol',
        'name',
        'price',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function stopAlerts() {
        return $this->hasMany(StopAlert::class);
    }

    public function users() {
        return $this->belongsToMany(User::class, 'stop_alerts', 'symbol', 'user_id');
    }
}
