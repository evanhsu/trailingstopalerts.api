<?php

namespace Tests\Unit;

use App\Domain\Stock;
use App\Domain\StopAlert;
use App\Domain\User;
use App\Infrastructure\Services\StopAlertService;
use App\Notifications\UserStopAlertTriggered;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Mockery;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
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

    public $stock1quote1;
    public $stock1quote2;

    public $stopAlertInitial;
    public $stopAlertUpdated;

    public $stopAlertInitialTriggerPrice;
    public $stopAlertUpdatedTriggerPrice;

    public function setUp()
    {
        parent::setUp();

        $this->user = new User([
            'name' => 'Test User',
            'email' => 'testuser@example.com',
            'password' => '$2y$10$itSk/qVY/MF67KLtfgRenOlYY8oCB7wHZkeogK7y6/NMwvkCiyk/6', // 'password'
        ]);
        $this->user->id = 9999;
        $this->user->save();
        $this->be($this->user); // Authenticate as this fake user

        $this->stock1quote1 = new Stock([
            'symbol' => 'MSFT',
            'open' => 100.00,
            'close' => 91.00,
            'high' => 101.25,
            'low' => 90.50,
            'quote_updated_at' => Carbon::parse('yesterday'),
        ]);

        $this->stopAlertInitial = [
            'symbol' => 'MSFT',
            'initial_price' => 100.10,
            'purchase_date' => Carbon::today(),
            'trail_amount' => 5.0,
            'trail_amount_units' => 'percent',
        ];
        $this->stopAlertInitialTriggerPrice = $this->stopAlertInitial['initial_price'] * (100 - 5.0) / 100.0; // 95.095

        $this->stopAlertUpdated = [
            'symbol' => 'MSFT',
            'trail_amount' => 9.5,
            'trail_amount_units' => 'percent',
        ];
        $this->stopAlertUpdatedTriggerPrice = $this->stopAlertInitial['initial_price'] * (100 - 9.5) / 100.0;
//        $this->stopAlertUpdatedTriggerPrice = $this->stock1quote1['high'] * (100 - 9.5) / 100.0; // 86.9705
    }

    private function getValidMock(string $methodName = 'firstOrCreate', $stockToReturn = null)
    {
        $stockService = Mockery::mock('App\Infrastructure\Services\StockService');
        $stockService->shouldReceive($methodName)->andReturn($stockToReturn);

        return new StopAlertService($stockService);
    }

    private function getInvalidMock($methodName)
    {
        $invalidResponse = null; // Returns null when invalid symbol is queried

        $stockService = Mockery::mock('App\Infrastructure\Services\StockService');
        $stockService->shouldReceive($methodName)->andReturnUsing(function() {
            throw new UnprocessableEntityHttpException();
        });

        return new StopAlertService($stockService);
    }

    private function createValidStopAlert()
    {
        $stopAlerts = $this->getValidMock('firstOrCreate', $this->stock1quote1);

        $stopAlertAttributes = array_merge(['user_id' => $this->user->id], $this->stopAlertInitial);

        return $stopAlerts->create($stopAlertAttributes);
    }

    private function createInvalidStopAlert()
    {
        $stopAlerts = $this->getinvalidMock('firstOrCreate');

        $stopAlertAttributes = [
            'user_id' => $this->user->id,
            'symbol' => 'FAKE',
            'initial_price' => 100.00,
            'trail_amount' => 5.0,
            'trail_amount_units' => 'percent',
        ];

        return $stopAlerts->create($stopAlertAttributes);
    }

    private function updateStopAlert($id)
    {
        $stopAlerts = $this->getValidMock();

        $stopAlertAttributes = array_merge(['user_id' => $this->user->id], $this->stopAlertUpdated);

        return $stopAlerts->update($id, $stopAlertAttributes);
    }

    private function destroyStopAlert($id)
    {
        $stopAlerts = $this->getValidMock('byStopAlert', $this->stock1quote1);

        return $stopAlerts->destroy($id);
    }

    public function testCreateValidStopAlert()
    {
        $this->assertEquals(0, StopAlert::where('symbol', $this->stock1quote1->symbol)
            ->where('user_id', $this->user->id)
            ->count()
        );
        $this->createValidStopAlert();
        $this->assertEquals(1, StopAlert::where('symbol', $this->stock1quote1->symbol)
            ->where('user_id', $this->user->id)
            ->count()
        );

        $stopAlert = StopAlert::where('symbol', $this->stock1quote1->symbol)->where('user_id', $this->user->id)->first();
        $this->assertEquals(round($this->stopAlertInitial['trail_amount'], 3), round($stopAlert->trail_amount, 3));
        $this->assertEquals('percent', $stopAlert->trail_amount_units);
        $this->assertEquals(round($this->stopAlertInitialTriggerPrice, 3), round($stopAlert->trigger_price, 3));
    }

    /**
     * @expectedException \Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException
     */
    public function testCreateInvalidStopAlert()
    {
        $this->assertEquals(0, StopAlert::where('symbol', $this->stock1quote1->symbol)
            ->where('user_id', $this->user->id)
            ->count()
        );
        $this->createInvalidStopAlert();
        $this->assertEquals(0, StopAlert::where('symbol', $this->stock1quote1->symbol)
            ->where('user_id', $this->user->id)
            ->count()
        );
    }

//    public function testEventIsFiredWhenStopAlertIsCreated()
//    {
//        $stopAlert = $this->createValidStopAlert();
//        Event::assertDispatched(StopAlertCreated::class, function ($event) use ($stopAlert) {
//            return $event->stopAlert->id == $stopAlert->id;
//        });
//    }

//    public function testStockIsCreatedWhenStopAlertIsCreatedForNewStockSymbol()
//    {
//        // This can't be tested using a Mock StockService...
//        $this->assertEquals(0, Stock::where('symbol', $this->stock1quote1->symbol)->count());
//        $this->assertEquals(1, Stock::where('symbol', $this->stock1quote1->symbol)->count());
//    }


    public function testUpdateValidStopAlert()
    {
        $stopAlert = $this->createValidStopAlert();
        $this->updateStopAlert($stopAlert->id);

        $stopAlert = StopAlert::where('symbol', $this->stock1quote1->symbol)->where('user_id', $this->user->id)->first();

        $this->assertEquals(round($this->stopAlertUpdated['trail_amount'], 3), round($stopAlert->trail_amount, 3));
        $this->assertEquals('percent', $stopAlert->trail_amount_units);
        $this->assertEquals(round($this->stopAlertUpdatedTriggerPrice, 3), round($stopAlert->trigger_price, 3));
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
        $stopAlert1 = $this->createValidStopAlert();
        $stopAlert2 = $this->createValidStopAlert();

        $this->destroyStopAlert($stopAlert1->id);

        $this->assertEquals(1, StopAlert::where('symbol', $this->stock1quote1->symbol)
            ->where('user_id', $this->user->id)
            ->count()
        );
    }

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
        $this->createValidStopAlert();

        $userAlerts = $stopAlerts->forUser($this->user->id);

        $this->assertEquals(2, $userAlerts->count());
    }

    public function testTrigger()
    {
        Notification::fake();

        $stopAlerts = $this->getValidMock();

        $stopAlert = $this->createValidStopAlert();
        $this->assertEquals(false, $stopAlert->triggered);

        $stopAlerts->trigger($stopAlert);
        $this->assertEquals(true, $stopAlert->fresh()->triggered);

        Notification::assertSentTo(
            $stopAlert->user,
            UserStopAlertTriggered::class,
            function ($notification, $channels) use ($stopAlert) {
                return $notification->stopAlert->id === $stopAlert->id;
            }
        );
    }
}
