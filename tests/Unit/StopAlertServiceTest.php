<?php

namespace Tests\Unit;

use App\Domain\Stock;
use App\Domain\StopAlert;
use App\Domain\User;
use App\Infrastructure\Services\StopAlertService;
use App\Domain\StockQuote;
use Illuminate\Support\Facades\Event;
use Mockery;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

class StopAlertServiceTest extends TestCase
{
    use MockeryPHPUnitIntegration, RefreshDatabase;
    
    /**
     * @var StopAlertService $stopAlerts
     */
    public $stopAlerts;

    /**
     * @var User $user
     */
    public $user;
    
    public function setUp()
    {
        parent::setUp();

        $alphaVantageResponse = collect([new StockQuote('FAKE', 96.18)]);

        $alphaVantage = Mockery::mock('App\Infrastructure\Services\AlphaVantage');
        $alphaVantage->shouldReceive('batchQuote')->andReturn($alphaVantageResponse);
        $this->stopAlerts = new StopAlertService($alphaVantage);

        $this->user = new User([
            'name'  => 'Test User',
            'email' => 'test@example.com',
            'password'  => '$2y$10$itSk/qVY/MF67KLtfgRenOlYY8oCB7wHZkeogK7y6/NMwvkCiyk/6', // 'password'
        ]);
        $this->user->id = 9999;
        $this->user->save();
        $this->be($this->user); // Authenticate as this fake user
    }

    private function createStopAlert() {
        Event::fake();

        $stopAlertAttributes = [
            'user_id'           => 9999,
            'symbol'            => 'FAKE',
            'trail_amount'      => 5.0,
            'trail_amount_units'=> 'percent',
        ];

        return $this->stopAlerts->create($stopAlertAttributes);
    }

    private function updateStopAlert($id) {
        Event::fake();

        $stopAlertAttributes = [
            'symbol'             => 'FAKE',
            'trail_amount'       => 9.5,
            'trail_amount_units' => 'percent',
        ];

        return $this->stopAlerts->update($id, $stopAlertAttributes);
    }

    private function destroyStopAlert($id) {
        Event::fake();

        return $this->stopAlerts->destroy($id);
    }

    public function testCreateStopAlert()
    {
        $this->assertEquals(0, StopAlert::where('symbol', 'FAKE')->where('user_id', $this->user->id)->count());
        $stopAlert = $this->createStopAlert();
        $this->assertEquals(1, StopAlert::where('symbol', 'FAKE')->where('user_id', $this->user->id)->count());
    }

    public function testEventIsFiredWhenStopAlertIsCreated()
    {
        $stopAlert = $this->createStopAlert();
        Event::assertDispatched(StopAlertCreated::class, function ($event) use ($stopAlert) {
            return $event->stopAlert->id == $stopAlert->id;
        });
    }

    public function testStockIsCreatedWhenStopAlertIsCreatedForNewStockSymbol()
    {
        $this->assertEquals(0, Stock::where('symbol', 'FAKE')->count());
        $stopAlert = $this->createStopAlert();
        $this->assertEquals(1, Stock::where('symbol', 'FAKE')->count());
    }


    public function testUpdateStopAlert()
    {
        $stopAlert = $this->createStopAlert();
        $stopAlert = $this->updateStopAlert($stopAlert->id);
        $this->assertEquals(9.5, StopAlert::where('symbol', 'FAKE')->where('user_id', $this->user->id)->first()->trail_amount);
    }

    public function testEventIsFiredWhenStopAlertIsUpdated()
    {
        $stopAlert = $this->createStopAlert();
        $stopAlert = $this->updateStopAlert($stopAlert->id);
        Event::assertDispatched(StopAlertUpdated::class, function ($event) use ($stopAlert) {
            return ($event->stopAlert->id == $stopAlert->id) && ($event->stopAlert->trail_amount == 9.5);
        });
    }


    public function testDeleteStopAlert()
    {
        $stopAlert = $this->createStopAlert();
        $stopAlertId = $stopAlert->id;

        $this->destroyStopAlert($stopAlert->id);
        $this->assertEquals(0, StopAlert::where('symbol', 'FAKE')->where('user_id', $this->user->id)->count());
    }

    public function testEventIsFiredWhenStopAlertIsDestroyed()
    {
        $stopAlert = $this->createStopAlert();
        $stopAlertId = $stopAlert->id;

        $this->destroyStopAlert($stopAlert->id);
        Event::assertDispatched(StopAlertDestroyed::class, function ($event) use ($stopAlertId) {
            return $event->stopAlertId == $stopAlertId;
        });
    }
}
