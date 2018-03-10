<?php
namespace App\Infrastructure\Services;

use App\Domain\Stock;
use App\Domain\StopAlert;
use App\Domain\User;
use DateTime;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class StopAlertService
{
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
     */
    public function create($attributes) {
        if(is_null($stock = Stock::find($attributes['symbol']))) {
            // TODO: create a new Stock with name & current price from the Alpha Vantage API
        }

        $attributes['high_price'] = $stock->price;
        $attributes['high_price_updated_at'] = new DateTime();

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
