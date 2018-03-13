<?php

namespace App\Listeners;

use App\Events\StockUpdated;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class RecalculateTriggerPrices
{
    /**
     * @var StockUpdated $event
     */
    public $event;

    /**
     * @param StockUpdated $event
     */
    public function __construct(StockUpdated $event)
    {
        $this->event = $event;
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        // Find all affected StopAlerts
        // Recalculate high_price, trigger_price, and triggered
        // Dispatch job NotifyUserAboutTriggeredAlert for each triggered StopAlert
    }
}
