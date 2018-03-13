<?php

namespace Tests\Unit;

use App\Domain\Stock;
use App\Domain\StopAlert;
use App\Domain\User;
use App\Infrastructure\Services\StockService;
use App\Infrastructure\Services\StopAlertService;
use App\Domain\StockQuote;
use App\Jobs\NotifyUserAboutTriggeredAlert;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Mockery;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

class StopAlertServiceTest extends TestCase
{
    use MockeryPHPUnitIntegration, RefreshDatabase;

    /**
     * @var User $user
     */
    public $user;

    public $stockSymbol = 'FAKE';
    public $stockSymbol2 = 'WRONG';
    public $stockPrice = 96.18;
    public $trailAmountOnCreate = 5.0;
    public $trailAmountOnUpdate = 9.5;
    public $triggerPriceOnCreate;
    public $triggerPriceOnUpdate;

    public function setUp()
    {
        parent::setUp();

        $this->user = new User([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => '$2y$10$itSk/qVY/MF67KLtfgRenOlYY8oCB7wHZkeogK7y6/NMwvkCiyk/6', // 'password'
        ]);
        $this->user->id = 9999;
        $this->user->save();
        $this->be($this->user); // Authenticate as this fake user

        $this->triggerPriceOnCreate = $this->stockPrice * (100 - $this->trailAmountOnCreate) / 100.0;
        $this->triggerPriceOnUpdate = $this->stockPrice * (100 - $this->trailAmountOnUpdate) / 100.0;
    }

    private function getValidMock($symbol = null, $price = null)
    {
        if (is_null($symbol)) {
            $symbol = $this->stockSymbol;
        }
        if (is_null($price)) {
            $price = $this->stockPrice;
        }
        $validAlphaVantageResponse = collect([new StockQuote($symbol, $price)]);

        $alphaVantage = Mockery::mock('App\Infrastructure\Services\AlphaVantage');
        $alphaVantage->shouldReceive('batchQuote')->andReturn($validAlphaVantageResponse);

        $stockService = new StockService($alphaVantage);
        return new StopAlertService($stockService);
    }

    private function getInvalidMock()
    {
        $invalidAlphaVantageResponse = collect([]); // Returns empty collection when invalid symbol is queried

        $alphaVantage = Mockery::mock('App\Infrastructure\Services\AlphaVantage');
        $alphaVantage->shouldReceive('batchQuote')->andReturn($invalidAlphaVantageResponse);

        $stockService = new StockService($alphaVantage);
        return new StopAlertService($stockService);
    }

    private function createValidStopAlert()
    {
        $stopAlerts = $this->getValidMock($this->stockSymbol, $this->stockPrice);

        $stopAlertAttributes = [
            'user_id' => $this->user->id,
            'symbol' => $this->stockSymbol,
            'trail_amount' => $this->trailAmountOnCreate,
            'trail_amount_units' => 'percent',
        ];

        return $stopAlerts->create($stopAlertAttributes);
    }

    private function createAnotherValidStopAlert()
    {
        $stopAlerts = $this->getValidMock($this->stockSymbol2, $this->stockPrice);

        $stopAlertAttributes = [
            'user_id' => $this->user->id,
            'symbol' => $this->stockSymbol2,
            'trail_amount' => $this->trailAmountOnCreate,
            'trail_amount_units' => 'percent',
        ];

        return $stopAlerts->create($stopAlertAttributes);
    }

    private function createInvalidStopAlert()
    {
        $stopAlerts = $this->getinvalidMock();

        $stopAlertAttributes = [
            'user_id' => $this->user->id,
            'symbol' => $this->stockSymbol,
            'trail_amount' => $this->trailAmountOnCreate,
            'trail_amount_units' => 'percent',
        ];

        return $stopAlerts->create($stopAlertAttributes);
    }

    private function updateStopAlert($id)
    {
        $stopAlerts = $this->getValidMock($this->stockSymbol, $this->stockPrice);

        $stopAlertAttributes = [
            'symbol' => $this->stockSymbol,
            'trail_amount' => $this->trailAmountOnUpdate,
            'trail_amount_units' => 'percent',
        ];

        return $stopAlerts->update($id, $stopAlertAttributes);
    }

    private function destroyStopAlert($id)
    {
        $stopAlerts = $this->getValidMock();

        return $stopAlerts->destroy($id);
    }

    public function testCreateValidStopAlert()
    {
        $this->assertEquals(0, StopAlert::where('symbol', $this->stockSymbol)->where('user_id', $this->user->id)->count());
        $stopAlert = $this->createValidStopAlert();
        $this->assertEquals(1, StopAlert::where('symbol', $this->stockSymbol)->where('user_id', $this->user->id)->count());

        $stopAlert = StopAlert::where('symbol', $this->stockSymbol)->where('user_id', $this->user->id)->first();
        $this->assertEquals(round($this->trailAmountOnCreate, 3), round($stopAlert->trail_amount, 3));
        $this->assertEquals('percent', $stopAlert->trail_amount_units);
        $this->assertEquals(round($this->triggerPriceOnCreate, 3), round($stopAlert->trigger_price, 3));
    }

    /**
     * @expectedException \Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException
     */
    public function testCreateInvalidStopAlert()
    {
        $this->assertEquals(0, StopAlert::where('symbol', $this->stockSymbol)->where('user_id', $this->user->id)->count());
        $stopAlert = $this->createInvalidStopAlert();
        $this->assertEquals(0, StopAlert::where('symbol', $this->stockSymbol)->where('user_id', $this->user->id)->count());
    }

//    public function testEventIsFiredWhenStopAlertIsCreated()
//    {
//        $stopAlert = $this->createValidStopAlert();
//        Event::assertDispatched(StopAlertCreated::class, function ($event) use ($stopAlert) {
//            return $event->stopAlert->id == $stopAlert->id;
//        });
//    }

    public function testStockIsCreatedWhenStopAlertIsCreatedForNewStockSymbol()
    {
        $this->assertEquals(0, Stock::where('symbol', $this->stockSymbol)->count());
        $stopAlert = $this->createValidStopAlert();
        $this->assertEquals(1, Stock::where('symbol', $this->stockSymbol)->count());
    }


    public function testUpdateValidStopAlert()
    {
        $stopAlert = $this->createValidStopAlert();
        $this->updateStopAlert($stopAlert->id);

        $stopAlert = StopAlert::where('symbol', $this->stockSymbol)->where('user_id', $this->user->id)->first();

        $this->assertEquals(round($this->trailAmountOnUpdate, 3), round($stopAlert->trail_amount, 3));
        $this->assertEquals('percent', $stopAlert->trail_amount_units);
        $this->assertEquals(round($this->triggerPriceOnUpdate, 3), round($stopAlert->trigger_price, 3));
    }

    /**
     * @expectedException \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function testUpdateInvalidStopAlert()
    {
        $stopAlert = $this->createValidStopAlert();
        $this->updateStopAlert($stopAlert->id + 1); // throw ModelNotFoundException
    }

//    public function testEventIsFiredWhenStopAlertIsUpdated()
//    {
//        $stopAlert = $this->createValidStopAlert();
//        $stopAlert = $this->updateStopAlert($stopAlert->id);
//        Event::assertDispatched(StopAlertUpdated::class, function ($event) use ($stopAlert) {
//            return ($event->stopAlert->id == $stopAlert->id) && ($event->stopAlert->trail_amount == $this->trailAmountOnUpdate);
//        });
//    }


    public function testDeleteStopAlert()
    {
        $stopAlert = $this->createValidStopAlert();
        $stopAlertId = $stopAlert->id;

        $this->destroyStopAlert($stopAlert->id);
        $this->assertEquals(0, StopAlert::where('symbol', $this->stockSymbol)->where('user_id', $this->user->id)->count());
    }

//    public function testEventIsFiredWhenStopAlertIsDestroyed()
//    {
//        $stopAlert = $this->createValidStopAlert();
//        $stopAlertId = $stopAlert->id;
//
//        $this->destroyStopAlert($stopAlert->id);
//        Event::assertDispatched(StopAlertDestroyed::class, function ($event) use ($stopAlertId) {
//            return $event->stopAlertId == $stopAlertId;
//        });
//    }

    /**
     * @expectedException \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function testFindByIdOrFail()
    {
        $stopAlerts = $this->getValidMock();
        $stopAlert = $this->createValidStopAlert();
        $this->assertEquals($stopAlert->id, $stopAlerts->byIdOrFail($stopAlert->id)->id);

        $nonExistentId = 89427;
        $stopAlerts->byIdOrFail($nonExistentId); // Should throw ModelNotFoundException
    }

    public function testCreateMultipleStopAlertsWithTheSameSymbol()
    {
        $this->createValidStopAlert();
        $this->createValidStopAlert();

        $this->assertEquals(2, StopAlert::all()->count());

    }

    public function testFindStopAlertsForUser()
    {
        $stopAlerts = $this->getValidMock();
        $this->createValidStopAlert();
        $this->createAnotherValidStopAlert();

        $userAlerts = $stopAlerts->forUser($this->user->id);

        $this->assertEquals(2, $userAlerts->count());
    }

    public function testTrigger()
    {
        Queue::fake();

        $stopAlerts = $this->getValidMock();

        $stopAlert = $this->createValidStopAlert();
        $this->assertEquals(false, $stopAlert->triggered);

        $stopAlerts->trigger($stopAlert);
        $this->assertEquals(true, $stopAlert->fresh()->triggered);

        Queue::assertPushed(NotifyUserAboutTriggeredAlert::class, function ($job) use ($stopAlert) {
            return $job->stopAlert->id === $stopAlert->id;
        });
    }
}
