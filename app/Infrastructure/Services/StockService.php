<?php

namespace App\Infrastructure\Services;

use App\Domain\Stock;
use App\Events\StockUpdated;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class StockService
{
    /**
     * @var AlphaVantage $client
     */
    protected $client;

    /**
     * StopAlertService constructor.
     * @param AlphaVantage $alphaVantage
     */
    public function __construct(AlphaVantage $alphaVantage)
    {
        $this->client = $alphaVantage;
    }

    /**
     * Uniqueness enforced by db
     *
     * @param string $symbol
     * @return Stock
     * @throws \Exception|UnprocessableEntityHttpException
     */
    public function create(string $symbol)
    {
        $quote = $this->client->dailyQuote($symbol);
        if(null === $quote) {
            throw new UnprocessableEntityHttpException("The symbol '$symbol' couldn't be found (is it on the NYSE?)");
        }

        return Stock::create([
            'symbol' => $quote->symbol,
            'open' => $quote->open,
            'close' => $quote->close,
            'high' => $quote->high,
            'low' => $quote->low,
            'quote_updated_at' => $quote->quote_updated_at,
        ]);
    }

    /**
     * @param string $symbol
     * @return Stock
     * @throws \Exception
     */
    public function firstOrCreate(string $symbol)
    {
        $stock = Stock::whereSymbol($symbol)->first();
        if (null === $stock) {
            $stock = $this->create($symbol);
        }

        return $stock;
    }

    /**
     * @param string $symbol
     * @param array $attributes
     * @return Stock
     */
    public function update(string $symbol, array $attributes)
    {
        $stock = $this->bySymbolOrFail($symbol);

        if ($stock->update($attributes)) {
            event(new StockUpdated($stock));
            return $stock;
        }

        throw new UnprocessableEntityHttpException("Couldn't update Stock: $symbol");
    }

    /**
     * @param string $symbol
     * @param array $attributes
     * @return Stock
     * @throws \Exception
     */
    public function updateOrCreate(string $symbol, array $attributes = [])
    {
        $stock = $this->bySymbol($symbol);

        if(null === $stock) {
            $stock = $this->create($symbol);
            return $stock;
        }

        if ($stock->update($attributes)) {
            event(new StockUpdated($stock));
            return $stock;
        }

        throw new UnprocessableEntityHttpException("Couldn't update Stock: $symbol");
    }


    /**
     * @param string $symbol
     * @return Stock
     */
    public function bySymbol($symbol)
    {
        return Stock::where('symbol', 'like', $symbol)->first();
    }

    /**
     * @param string $symbol
     * @return Stock
     */
    public function bySymbolOrFail($symbol)
    {
        if (is_null($stock = Stock::where('symbol', 'like', $symbol)->first())) {
            throw new ModelNotFoundException;
        }

        return $stock;
    }

    /**
     * @param $stock
     * @return bool
     * @throws \Exception
     */
    public function destroy($stock)
    {
        if ($stock instanceof Stock) {
            $stock->delete();
        } else {
            Stock::destroy($stock);
        }
        return true;
    }
}