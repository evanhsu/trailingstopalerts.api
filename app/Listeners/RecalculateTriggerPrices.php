<?php

namespace App\Listeners;

use App\Events\StockUpdated;
use App\Infrastructure\Services\StopAlertService;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class RecalculateTriggerPrices
{
    /**
     * @var StopAlertService $stopAlerts
     */
    protected $stopAlerts;

    public function __construct(StopAlertService $stopAlerts)
    {
        $this->stopAlerts = $stopAlerts;
    }

    /**
     * Handle the event.
     *
     * @param StockUpdated $event
     * @return void
     */
    public function handle(StockUpdated $event)
    {
        $stock = $event->stock;

        $stock->stopAlerts->each(function ($stopAlert) use ($stock) {

            // Recalculate high_price, trigger_price, and triggered
            if ($stock->price > $stopAlert->high_price) {
                $this->stopAlerts->update($stopAlert->id, [
                    'high_price' => $stock->price,
                    'high_price_updated_at' => $stock->quote_updated_at,
                    // trigger_price is automatically updated
                ]);
            }

            if ($stock->price <= $stopAlert->trigger_price) {
                $this->stopAlerts->trigger($stopAlert);
            }
        });
    }
}
