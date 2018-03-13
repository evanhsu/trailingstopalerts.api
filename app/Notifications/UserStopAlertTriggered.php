<?php

namespace App\Notifications;

use App\Domain\StopAlert;
use App\Infrastructure\Services\StopAlertService;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class UserStopAlertTriggered extends Notification // implements ShouldQueue
{
    use Queueable;

    /**
     * @var StopAlert $stopAlert
     */
    public $stopAlert;

    /**
     * Create a new notification instance.
     *
     * @param StopAlert $stopAlert
     */
    public function __construct(StopAlert $stopAlert)
    {
        $this->stopAlert = $stopAlert;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $symbol = $this->stopAlert->symbol;
        $initialPrice = $this->stopAlert->initial_price;
        $initialPriceDate = $this->stopAlert->created_at->toFormattedDateString();
        $price = $this->stopAlert->stock->price;
        $trailString = ($this->stopAlert->trail_amount_units == 'dollars' ? '$' : '') . $this->stopAlert->trail_amount . ($this->stopAlert->trail_amount_units == 'percent' ? '%' : '');
        $highPrice = $this->stopAlert->high_price;
        $highPriceDate = $this->stopAlert->high_price_updated_at->toFormattedDateString();
        $profit = $this->stopAlert->profit();
        $profit = $profit >= 0 ? "\$$profit" : "-$" . $profit * -1;

        return (new MailMessage)
                    ->subject("Trailing Stop Alert: $symbol has dropped to $price")
                    ->line("Your trailing stop for $symbol has been reached.")
                    ->line("The highest price reached since you created your alert was \$$highPrice on $highPriceDate")
                    ->line("The current price of \$$price is at least $trailString below the high.")
                    ->action('Clear or reset this alert', url('/'))
                    ->line("Summary:")
                    ->line("Initial Price: \$$initialPrice on $initialPriceDate")
                    ->line("High Price: \$$highPrice on $highPriceDate")
                    ->line("Unrealized Profit/Loss: $profit");
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'id' => $this->stopAlert->id,
            'symbol' => $this->stopAlert->symbol,
            'price' => $this->stopAlert->stock->price,
            'high_price' => $this->stopAlert->high_price,
            'high_price_updated_at' => $this->stopAlert->high_price_updated_at->toIso8601String(),
            'trail_amount' => $this->stopAlert->trail_amount,
            'trail_amount_units' => $this->stopAlert->trail_amount_units,
            'created_at' => $this->stopAlert->created_at->toIso8601String(),
        ];
    }
}
