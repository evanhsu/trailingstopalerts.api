<?php

namespace App\Jobs;

use App\Domain\StopAlert;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class NotifyUserAboutTriggeredAlert implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var StopAlert $stopAlert
     */
    public $stopAlert;

    /**
     * Create a new job instance.
     *
     * @param StopAlert $stopAlert
     */
    public function __construct(StopAlert $stopAlert)
    {
        $this->stopAlert = $stopAlert;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //
    }
}
