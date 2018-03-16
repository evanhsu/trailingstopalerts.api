<?php

namespace Tests\Unit;

use App\Domain\Stock;
use App\Domain\StockQuote;
use App\Events\StockUpdated;
use App\Infrastructure\Services\StockService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Event;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StockServiceTest extends TestCase
{
    use MockeryPHPUnitIntegration, RefreshDatabase;

    /**
     * @var Mockery\MockInterface
     */
    protected $alphaVantage;
    public $stock1quote1;
    public $stock1quote2;

    public function setUp()
    {
        parent::setUp();

        $this->alphaVantage = Mockery::mock('App\Infrastructure\Services\AlphaVantage');

        $this->stock1quote1 = [
            'symbol' => 'MSFT',
            'open' => 100.00,
            'close' => 91.00,
            'high' => 101.25,
            'low' => 90.50,
            'quote_updated_at' => Carbon::parse('yesterday'),
        ];

        $this->stock1quote2 = [
            'symbol' => 'MSFT',
            'open' => 90.50,
            'close' => 95.00,
            'high' => 96.10,
            'low' => 89.90,
            'quote_updated_at' => Carbon::parse('today'),
        ];
    }

    private function getValidStockServiceMock()
    {
        $alphaVantageValidResponse1 = new Stock($this->stock1quote1);
        $alphaVantageValidResponse2 = new Stock($this->stock1quote2);

        $this->alphaVantage->shouldReceive('dailyQuote')
            ->andReturn($alphaVantageValidResponse1, $alphaVantageValidResponse2); // 2 valid responses are queued up

        return new StockService($this->alphaVantage);
    }

    private function getInvalidStockServiceMock()
    {
        $alphaVantageInvalidResponse = null;

        $this->alphaVantage->shouldReceive('dailyQuote')
            ->andReturn($alphaVantageInvalidResponse);

        return new StockService($this->alphaVantage);
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function testCreateValidStock()
    {
        $stocks = $this->getValidStockServiceMock();

        $stock = $stocks->create($this->stock1quote1['symbol']);

//        fwrite(STDERR, print_r($now), TRUE); // Echo to PHPUnit console

        $this->assertEquals(1, Stock::whereSymbol($this->stock1quote1['symbol'])->count());
        $this->assertEquals($this->stock1quote1['open'], Stock::whereSymbol($this->stock1quote1['symbol'])->first()->open);
        $this->assertEquals($this->stock1quote1['close'], Stock::whereSymbol($this->stock1quote1['symbol'])->first()->close);
        $this->assertEquals($this->stock1quote1['high'], Stock::whereSymbol($this->stock1quote1['symbol'])->first()->high);
        $this->assertEquals($this->stock1quote1['low'], Stock::whereSymbol($this->stock1quote1['symbol'])->first()->low);
        $this->assertTrue($this->stock1quote1['quote_updated_at']->diffInSeconds(Stock::whereSymbol($this->stock1quote1['symbol'])->first()->quote_updated_at) < 1);
    }

    /**
     * Exception should be thrown when attempting to create a stock with invalid symbol
     *
     * @expectedException \Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException
     */
    public function testCreateInvalidStock()
    {
        $invalidSymbol = 'INVALID';
        $stocks = $this->getInvalidStockServiceMock();

        $stock = $stocks->create($invalidSymbol);

        $this->assertNull($stock, 'StockService should return null when attempting to create an invalid symbol');
        $this->assertEquals(0, Stock::whereSymbol($invalidSymbol)->count());
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function testStockServiceFirstOrCreate()
    {
        $stocks = $this->getValidStockServiceMock();

        $stocks->firstOrCreate($this->stock1quote1['symbol']);

        $this->assertEquals(1, Stock::whereSymbol($this->stock1quote1['symbol'])->count());
        $this->assertEquals($this->stock1quote1['open'], Stock::whereSymbol($this->stock1quote1['symbol'])->first()->open);

        $stocks->firstOrCreate($this->stock1quote1['symbol']);

        $this->assertEquals(1, Stock::whereSymbol($this->stock1quote1['symbol'])->count());
        $this->assertEquals($this->stock1quote1['open'], Stock::whereSymbol($this->stock1quote1['symbol'])->first()->open); // Assert price did NOT update to $stock1quote2['price']
    }

    /**
     *
     */
    public function testStockServiceUpdateOrCreate()
    {
        $stocks = $this->getValidStockServiceMock();

        $this->assertEquals(0, Stock::whereSymbol($this->stock1quote1['symbol'])->count());

        $stocks->updateOrCreate($this->stock1quote1['symbol']);

        $this->assertEquals(1, Stock::whereSymbol($this->stock1quote1['symbol'])->count());
        $this->assertEquals($this->stock1quote1['open'], Stock::whereSymbol($this->stock1quote1['symbol'])->first()->open);

        $stocks->updateOrCreate($this->stock1quote1['symbol'], $this->stock1quote2);

        $this->assertEquals(1, Stock::whereSymbol($this->stock1quote1['symbol'])->count());
        $this->assertEquals($this->stock1quote2['open'], Stock::whereSymbol($this->stock1quote2['symbol'])->first()->open); // Assert price DID update to $stock1quote2['price']
    }
    /**
     * @return void
     * @throws \Exception
     */
    public function testUpdateStock()
    {
        Event::fake(StockUpdated::class); // ONLY intercept this event type

        $stocks = $this->getValidStockServiceMock();

        $stock = $stocks->create($this->stock1quote1['symbol']);
        $this->assertEquals(1, Stock::whereSymbol($this->stock1quote1['symbol'])->count());

        $stocks->update($this->stock1quote1['symbol'], $this->stock1quote2);
        $this->assertEquals(1, Stock::whereSymbol($this->stock1quote2['symbol'])->count());

        $stock = Stock::whereSymbol($this->stock1quote2['symbol'])->first();

        $this->assertEquals($this->stock1quote2['open'], $stock->open);
        $this->assertEquals($this->stock1quote2['close'], $stock->close);
        $this->assertEquals($this->stock1quote2['high'], $stock->high);
        $this->assertEquals($this->stock1quote2['low'], $stock->low);
        $this->assertTrue($this->stock1quote2['quote_updated_at']->diffInSeconds($stock->quote_updated_at) < 1);

        Event::assertDispatched(StockUpdated::class, function ($event) use ($stock) {
            return $event->stock->symbol == $stock->symbol;
        });
    }

    /**
     * @return void
     */
    public function testFindStockBySymbol()
    {
        $stocks = $this->getValidStockServiceMock();

        Stock::create($this->stock1quote1);

        $this->assertEquals(1, Stock::whereSymbol($this->stock1quote1['symbol'])->count());
        $this->assertEquals($this->stock1quote1['symbol'], $stocks->bySymbol($this->stock1quote1['symbol'])->symbol);
    }

    /**
     * @return void
     * @expectedException \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function testFindStockBySymbolOrFail()
    {
        $stocks = $this->getValidStockServiceMock();

        $this->assertEquals(0, Stock::whereSymbol($this->stock1quote1['symbol'])->count());
        $stock = $stocks->bySymbolOrFail($this->stock1quote1['symbol']); // Should throw exception
    }

    public function testDestroyStock()
    {
        $stocks = $this->getValidStockServiceMock();

        $stock = $stocks->create($this->stock1quote1['symbol']);

        $this->assertEquals(1, Stock::whereSymbol($this->stock1quote1['symbol'])->count());
        $stocks->destroy($stock); // Destroy by passing in Object
        $this->assertEquals(0, Stock::whereSymbol($this->stock1quote1['symbol'])->count());

        $stock = $stocks->create($this->stock1quote2['symbol']);

        $this->assertEquals(1, Stock::whereSymbol($this->stock1quote2['symbol'])->count());
        $stocks->destroy($stock->symbol); // Destroy by passing in a symbol (string)
        $this->assertEquals(0, Stock::whereSymbol($this->stock1quote2['symbol'])->count());
    }
}
