<?php

namespace App\Infrastructure\Services;

use App\Domain\StopAlert;
use App\Domain\User;
use App\Notifications\UserStopAlertTriggered;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;
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
     * @throws \Exception|UnprocessableEntityHttpException
     */
    public function create($attributes)
    {
        if(!isset($attributes['symbol'])) {
            throw new UnprocessableEntityHttpException('No symbol provided');
        }

        if(!isset($attributes['initial_price'])) {
            throw new UnprocessableEntityHttpException('No initial_price provided');
        }

        if(!isset($attributes['trail_amount'])) {
            throw new UnprocessableEntityHttpException('No trail_amount provided');
        }

        if(!isset($attributes['purchase_date'])) {
            throw new UnprocessableEntityHttpException('No purchase_date provided');
        }

        $stock = $this->stocks->firstOrCreate($attributes['symbol']);

        $defaults = [
            'purchase_date' => Carbon::today(),
            'trail_amount_units' => 'percent',
        ];

        // TODO: Set 'high_price' to the HIGHEST price between 'purchase_date' and 'today'
        if($attributes['initial_price'] > $stock->high) {
            $attributes['high_price'] = $attributes['initial_price'];
            $attributes['high_price_updated_at'] = $attributes['purchase_date'];
        } else {
            $attributes['high_price'] = $stock->high;
            $attributes['high_price_updated_at'] = $stock->quote_updated_at;
        }

        $stopAlert = StopAlert::create(array_merge($defaults, $attributes));
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
//            event(StopAlertUpdated, $stopAlert);
            return $stopAlert;
        }

        throw new UnprocessableEntityHttpException("Couldn't update stop alert");
    }

    /**
     * @param $stopAlertId
     * @return boolean
     * @throws \Exception
     */
    public function destroy($stopAlertId)
    {
        $stopAlert = $this->byId($stopAlertId);
        $stock = $this->stocks->byStopAlert($stopAlert);

        StopAlert::destroy($stopAlertId);
//        event(StopAlertDestroyed, $stopAlertId);

        // Destroy the Stock as well, if there are no more StopAlerts using it.
        if ($stock->stopAlerts()->count() === 0) {
            $this->stocks->destroy($stock->symbol);
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

    /**
     * @param StopAlert|int $stopAlert
     * @return mixed
     */
    public function trigger($stopAlert) {
        if($stopAlert instanceof StopAlert) {
            $id = $stopAlert->id;
        } else {
            $id = $stopAlert;
        }

        // Don't send multiple notifications
        if(!$stopAlert->triggered) {
            $stopAlert = $this->update($id, [
                'triggered' => true,
            ]);

            $stopAlert->user->notify(new UserStopAlertTriggered($stopAlert));
        }

        return $stopAlert;
    }

    /**
     * @param StopAlert|int $stopAlert
     * @return float
     */
    public function profit($stopAlert) {
        if(!($stopAlert instanceof StopAlert)) {
            $stopAlert = $this->byIdOrFail($stopAlert);
        }

        return $stopAlert->stock->close - $stopAlert->initial_price;
    }
}
