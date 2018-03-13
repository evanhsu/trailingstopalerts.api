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
     * @var StockService $stocks
     */
    protected $stocks;

    /**
     * @param StockService $stocks
     */
    public function __construct(StockService $stocks)
    {
        $this->stocks = $stocks;
    }

    /**
     * @param $id
     * @return mixed
     */
    public function byId($id)
    {
        return StopAlert::find($id);
    }

    /**
     * @param $userId
     * @return mixed
     */
    public function forUser($userId)
    {
        return User::find($userId)->stopAlerts;
    }

    /**
     * @param $attributes
     * @return mixed
     * @throws \Exception
     */
    public function create($attributes)
    {
        $stock = $this->stocks->firstOrCreate($attributes['symbol']);

        $attributes['high_price'] = $stock->price;
        $attributes['high_price_updated_at'] = $stock->quote_updated_at;

        $stopAlert = StopAlert::create($attributes);
        //event(StopAlertCreated, $stopAlert);

        return $stopAlert;
    }

    /**
     * @param $id
     * @param $attributes
     * @return mixed
     */
    public function update($id, $attributes)
    {
        $stopAlert = $this->byIdOrFail($id);

        if ($stopAlert->update($attributes)) {
//            $stopAlert->updateTriggerPrice();
//            event(StopAlertUpdated, $stopAlert);
            return $stopAlert;
        }

        throw new UnprocessableEntityHttpException("Couldn't update stop alert");
    }

    /**
     * @param $stopAlertId
     * @return boolean
     */
    public function destroy($stopAlertId)
    {
        $stopAlert = $this->byId($stopAlertId);
        $stock = $stopAlert->stock;

        StopAlert::destroy($stopAlertId);
//        event(StopAlertDestroyed, $stopAlertId);

        // Destroy the Stock as well, if there are no more StopAlerts using it.
        if ($stock->stopAlerts()->count() === 0) {
            $this->stocks->destroy($stock->id);
        }

        return true;
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
