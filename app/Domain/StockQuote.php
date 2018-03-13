<?php

namespace App\Domain;

use Carbon\Carbon;

class StockQuote
{
    public $symbol;
    public $price;
    public $quote_updated_at;

    /**
     * StockQuote constructor.
     * @param string symbol
     * @param float $price
     * @param string|null $quote_updated_at
     * @param string $timezone
     */
    public function __construct($symbol, $price, $quote_updated_at = null, $timezone = 'US/Eastern')
    {
        $this->symbol = $symbol;
        $this->price = $price;
        if (is_null($quote_updated_at)) {
            $this->quote_updated_at = Carbon::now();
        } else {
            $this->quote_updated_at = Carbon::parse($quote_updated_at, $timezone);
        }
    }

    /**
     * @param mixed $quote_updated_at
     * @return StockQuote
     */
    public function setTimestamp($quote_updated_at)
    {
        $this->quote_updated_at = Carbon::parse($quote_updated_at);
        return $this;
    }

    public function toArray()
    {
        return [
            'symbol' => $this->symbol,
            'price' => $this->price,
            'quote_updated_at' => $this->quote_updated_at,
        ];
    }
}