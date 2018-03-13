<?php

namespace Tests\Unit;

use App\Domain\Stock;
use App\Domain\StockQuote;
use App\Infrastructure\Services\StockService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StockServiceTest extends TestCase
{
    use MockeryPHPUnitIntegration, RefreshDatabase;

    /**
     * @var Mockery\MockInterface
     */
    protected $alphaVantage;
    public $stockSymbol = 'FAKE';
    public $stockPrice1 = 96.18;
    public $stockTimestamp1;
    public $stockPrice2 = 80.50;
    public $stockTimestamp2;

    public function setUp()
    {
        parent::setUp();

        $this->alphaVantage = Mockery::mock('App\Infrastructure\Services\AlphaVantage');
    }

    private function getValidStockServiceMock()
    {
        $this->stockTimestamp1 = Carbon::now()->subDay();
        $this->stockTimestamp2 = Carbon::now();
        $alphaVantageValidResponse1 = collect([new StockQuote($this->stockSymbol, $this->stockPrice1, $this->stockTimestamp1)]);
        $alphaVantageValidResponse2 = collect([new StockQuote($this->stockSymbol, $this->stockPrice2, $this->stockTimestamp2)]);

        $this->alphaVantage->shouldReceive('batchQuote')
            ->andReturn($alphaVantageValidResponse1, $alphaVantageValidResponse2); // 2 valid responses are queued up

        return new StockService($this->alphaVantage);
    }

    private function getInvalidStockServiceMock()
    {
        $alphaVantageInvalidResponse = collect([]);

        $this->alphaVantage->shouldReceive('batchQuote')
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

        $stock = $stocks->create($this->stockSymbol);

//        fwrite(STDERR, print_r($now), TRUE); // Echo to PHPUnit console

        $this->assertEquals(1, Stock::whereSymbol($this->stockSymbol)->count());
        $this->assertEquals($this->stockPrice1, Stock::whereSymbol($this->stockSymbol)->first()->price);
        $this->assertTrue($this->stockTimestamp1->diffInSeconds(Stock::whereSymbol($this->stockSymbol)->first()->quote_updated_at) < 1);
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

        $this->assertEquals(0, Stock::whereSymbol($invalidSymbol)->count());
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function testStockServiceFirstOrCreate()
    {
        $stocks = $this->getValidStockServiceMock();

        $stocks->firstOrCreate($this->stockSymbol);

        $this->assertEquals(1, Stock::whereSymbol($this->stockSymbol)->count());
        $this->assertEquals($this->stockPrice1, Stock::whereSymbol($this->stockSymbol)->first()->price);

        $stocks->firstOrCreate($this->stockSymbol);

        $this->assertEquals(1, Stock::whereSymbol($this->stockSymbol)->count());
        $this->assertEquals($this->stockPrice1, Stock::whereSymbol($this->stockSymbol)->first()->price); // Assert price did NOT update to 'stockPrice2'
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function testUpdateStock()
    {
        $stocks = $this->getValidStockServiceMock();

        $stock = $stocks->create($this->stockSymbol);
        $this->assertEquals(1, Stock::whereSymbol($this->stockSymbol)->count());

        $stocks->update($this->stockSymbol, [
            'price' => $this->stockPrice2,
            'quote_updated_at' => $this->stockTimestamp2,
        ]);
        $this->assertEquals(1, Stock::whereSymbol($this->stockSymbol)->count());

        $stock = Stock::whereSymbol($this->stockSymbol)->first();

        $this->assertEquals($this->stockPrice2, $stock->price);
        $this->assertTrue($this->stockTimestamp2->diffInSeconds($stock->quote_updated_at) < 1);
    }

    /**
     * @return void
     */
    public function testFindStockBySymbol()
    {
        $stocks = $this->getValidStockServiceMock();

        Stock::create([
            'symbol' => $this->stockSymbol,
            'price' => $this->stockPrice1,
            'quote_updated_at' => $this->stockTimestamp1,
        ]);

        $this->assertEquals(1, Stock::whereSymbol($this->stockSymbol)->count());
        $this->assertEquals($this->stockSymbol, $stocks->bySymbol($this->stockSymbol)->symbol);
    }

    /**
     * @return void
     * @expectedException \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function testFindStockBySymbolOrFail()
    {
        $stocks = $this->getValidStockServiceMock();

        $this->assertEquals(0, Stock::whereSymbol($this->stockSymbol)->count());
        $stock = $stocks->bySymbolOrFail($this->stockSymbol); // Should throw exception
    }

    public function testDestroyStock()
    {
        $stocks = $this->getValidStockServiceMock();

        $stock = $stocks->create($this->stockSymbol);

        $this->assertEquals(1, Stock::whereSymbol($this->stockSymbol)->count());
        $stocks->destroy($stock); // Destroy by passing in Object
        $this->assertEquals(0, Stock::whereSymbol($this->stockSymbol)->count());

        $stock = $stocks->create($this->stockSymbol);

        $this->assertEquals(1, Stock::whereSymbol($this->stockSymbol)->count());
        $stocks->destroy($stock->symbol); // Destroy by passing in a symbol
        $this->assertEquals(0, Stock::whereSymbol($this->stockSymbol)->count());
    }
}
