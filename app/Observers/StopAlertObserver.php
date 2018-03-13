<?php

namespace App\Observers;

use App\Domain\StopAlert;

class StopAlertObserver
{
   public function creating(StopAlert $stopAlert)
   {
       $stopAlert->updateTriggerPrice();
   }

    public function saving(StopAlert $stopAlert)
    {
        $stopAlert->updateTriggerPrice();
    }
}