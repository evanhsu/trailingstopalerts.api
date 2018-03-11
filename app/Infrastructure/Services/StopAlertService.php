<?php
namespace App\Infrastructure\Services;

use App\Domain\Stock;
use App\Domain\StopAlert;
use App\Domain\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class StopAlertService
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
     * @param $id
     * @return mixed
     */
    public function byId($id) {
        return StopAlert::find($id);
    }

    /**
     * @param $userId
     * @return mixed
     */
    public function forUser($userId) {
        return User::find($userId)->stopAlerts;
    }

    /**
     * @param $attributes
     * @return mixed
     * @throws \Exception
     */
    public function create($attributes) {
        if(is_null($stock = Stock::find(strtoupper($attributes['symbol'])))) {
            $quote = $this->client->batchQuote($attributes['symbol']);
            if($quote->count() == 0) {
                throw new UnprocessableEntityHttpException("The symbol '".$attributes['symbol']."' couldn't be found (is it on the NYSE?)");
            }

            $stock = Stock::create([
                'symbol'            => $quote->first()->symbol,
                'price'             => $quote->first()->price,
                'quote_updated_at'  => $quote->first()->timestamp,
            ]);
        }

        $attributes['high_price'] = $stock->price;
        $attributes['high_price_updated_at'] = $stock->quote_updated_at;

        return StopAlert::create($attributes);
    }

    /**
     * @param $id
     * @param $attributes
     * @return mixed
     */
    public function update($id, $attributes) {
        $stopAlert = $this->byIdOrFail($id);
        if($stopAlert->update($attributes)) {
            return $stopAlert;
        }

        throw new UnprocessableEntityHttpException('Couldn\'t update alert');
    }

    /**
     * @param $stopAlertId
     * @return int
     */
    public function destroy($stopAlertId) {
        return StopAlert::destroy($stopAlertId);
    }

    /**
     * @param $stopAlertId
     * @return mixed
     */
    public function byIdOrFail($stopAlertId)
    {
        if (is_null($stopAlert = $this->byId($stopAlertId))) {
            throw new ModelNotFoundException();
        }

        return $stopAlert;
    }
}
