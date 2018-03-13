<?php

namespace App\Domain;

use Carbon\Carbon;

class StockQuote
{
    public $symbol;
    public $price;
    public $timestamp;

    /**
     * StockQuote constructor.
     * @param string symbol
     * @param float $price
     * @param string|null $timestamp
     * @param string $timezone
     */
    public function __construct($symbol, $price, $timestamp = null, $timezone = 'US/Eastern')
    {
        $this->symbol = $symbol;
        $this->price = $price;
        if (is_null($timestamp)) {
            $this->timestamp = Carbon::now();
        } else {
            $this->timestamp = Carbon::parse($timestamp, $timezone);
        }
    }

    /**
     * @param mixed $timestamp
     * @return StockQuote
     */
    public function setTimestamp($timestamp)
    {
        $this->timestamp = Carbon::parse($timestamp);
        return $this;
    }

    public function toArray()
    {
        return [
            'symbol' => $this->symbol,
            'price' => $this->price,
            'timestamp' => $this->timestamp,
        ];
    }
}