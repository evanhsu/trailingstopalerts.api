<?php

namespace Tests\Unit;

use App\Domain\User;
use App\Infrastructure\Services\StopAlertService;
use App\Domain\StockQuote;
use Illuminate\Support\Facades\Event;
use Mockery;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

class StockAlertEventsTest extends TestCase
{
    use MockeryPHPUnitIntegration, RefreshDatabase;
    
    /**
     * @var StopAlertService $stopAlerts
     */
    public $stopAlerts;
    
    public function setUp()
    {
        parent::setUp();

//        $guzzleResponse = json_decode('
//            {
//                "Meta Data": {
//                    "1. Information": "Batch Stock Market Quotes",
//                    "2. Notes": "IEX Real-Time Price provided for free by IEX (https://iextrading.com/developer/).",
//                    "3. Time Zone": "US/Eastern"
//                },
//                "Stock Quotes": [
//                    {
//                        "1. symbol": "FAKE",
//                        "2. price": "96.1800",
//                        "3. volume": "--",
//                        "4. timestamp": "2018-03-09 16:01:30"
//                    }
//                ]
//            }'
//        );

        $alphaVantageResponse = collect([new StockQuote('FAKE', 96.18)]);

        $alphaVantage = Mockery::mock('App\Infrastructure\Services\AlphaVantage');
        $alphaVantage->shouldReceive('batchQuote')->andReturn($alphaVantageResponse);
        $this->stopAlerts = new StopAlertService($alphaVantage);

        $user = new User([
            'name'  => 'Test User',
            'email' => 'test@example.com',
            'password'  => '$2y$10$itSk/qVY/MF67KLtfgRenOlYY8oCB7wHZkeogK7y6/NMwvkCiyk/6', // 'password'
        ]);
        $user->id = 9999;
        $user->save();
        $this->be($user); // Authenticate as this fake user
    }

    private function createStopAlert() {
        $stopAlertAttributes = [
            'user_id'           => 9999,
            'symbol'            => 'FAKE',
            'trail_amount'      => 5.0,
            'trail_amount_units'=> 'percent',
        ];

        return $this->stopAlerts->create($stopAlertAttributes);
    }

    private function updateStopAlert($id) {
        $stopAlertAttributes = [
            'symbol'             => 'FAKE',
            'trail_amount'       => 9.5,
            'trail_amount_units' => 'percent',
        ];

        return $this->stopAlerts->update($id, $stopAlertAttributes);
    }

    private function destroyStopAlert($id) {
        return $this->stopAlerts->destroy($id);
    }

    public function testEventIsFiredWhenStopAlertIsCreated()
    {
        Event::fake();

        $stopAlert = $this->createStopAlert();
        Event::assertDispatched(StopAlertCreated::class, function ($event) use ($stopAlert) {
            return $event->stopAlert->id == $stopAlert->id;
        });
    }

    public function testEventIsFiredWhenStopAlertIsUpdated()
    {
        Event::fake();

        $stopAlert = $this->createStopAlert();
        $stopAlert = $this->updateStopAlert($stopAlert->id);
        Event::assertDispatched(StopAlertUpdated::class, function ($event) use ($stopAlert) {
            return ($event->stopAlert->id == $stopAlert->id) && ($event->stopAlert->trail_amount == 9.5);
        });
    }

    public function testEventIsFiredWhenStopAlertIsDestroyed()
    {
        Event::fake();

        $stopAlert = $this->createStopAlert();
        $stopAlertId = $stopAlert->id;

        $this->destroyStopAlert($stopAlert->id);
        Event::assertDispatched(StopAlertDestroyed::class, function ($event) use ($stopAlertId) {
            return $event->stopAlertId == $stopAlertId;
        });
    }
}
