<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class StopAlertResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
//        return parent::toArray($request);
        return [
            'id' => $this->id,
            'symbol' => $this->symbol,
            'user_id' => $this->user_id,
            'trail_amount' => $this->trail_amount,
            'trail_amount_units' => $this->trail_amount_units,
            'initial_price' => $this->initial_price,
            'purchase_date' => $this->purchase_date->toDateString(),
            'high_price' => $this->high_price,
            'high_price_updated_at' => $this->high_price_updated_at->toDateString(),
            'trigger_price' => $this->trigger_price,
            'triggered' => $this->triggered,
        ];
    }
}
