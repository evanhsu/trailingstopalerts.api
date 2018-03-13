<?php

namespace App\Infrastructure\Services;

use App\Domain\Stock;
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
        $quote = $this->client->batchQuote($symbol);
        if ($quote->count() == 0) {
            throw new UnprocessableEntityHttpException("The symbol '$symbol' couldn't be found (is it on the NYSE?)");
        }

        $stockQuote = $quote->first();
        return Stock::create([
            'symbol' => $stockQuote->symbol,
            'price' => $stockQuote->price,
            'quote_updated_at' => $stockQuote->timestamp,
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
//            event(StockUpdated, $stock);
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