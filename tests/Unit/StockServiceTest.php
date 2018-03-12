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
    public $stockPrice = 96.18;

    public function setUp()
    {
        parent::setUp();

        $this->alphaVantage = Mockery::mock('App\Infrastructure\Services\AlphaVantage');
    }

    private function getValidStockServiceMock()
    {
        $alphaVantageValidResponse = collect([new StockQuote($this->stockSymbol, $this->stockPrice)]);

        $this->alphaVantage->shouldReceive('batchQuote')
            ->andReturn($alphaVantageValidResponse, $alphaVantageValidResponse);

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
        $now = Carbon::now();
        $stocks = $this->getValidStockServiceMock();

        $stock = $stocks->create([
            'symbol' => $this->stockSymbol,
            'price' => $this->stockPrice,
            'quote_updated_at' => $now,
        ]);

//        fwrite(STDERR, print_r($now), TRUE); // Echo to PHPUnit console

        $this->assertEquals(1, Stock::whereSymbol($this->stockSymbol)->count());
        $this->assertEquals($this->stockPrice, Stock::whereSymbol($this->stockSymbol)->first()->price);
        $this->assertTrue($now->diffInSeconds(Stock::whereSymbol($this->stockSymbol)->first()->quote_updated_at) < 1);
    }

    /**
     * Exception should be thrown when attempting to create a stock with invalid symbol
     *
     * @expectedException \Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException
     */
    public function testCreateInvalidStock()
    {
        $stocks = $this->getInvalidStockServiceMock();

        $stock = $stocks->create([
            'symbol' => 'INVALIDSYMBOL',
            'price' => 5.25,
            'quote_updated_at' => Carbon::now(),
        ]);

        $this->assertEquals(0, Stock::whereSymbol($this->stockSymbol)->count());
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function testStockServiceFirstOrCreate()
    {
        $now = Carbon::now();
        $stocks = $this->getValidStockServiceMock();

        $stocks->firstOrCreate($this->stockSymbol, [
            'symbol' => $this->stockSymbol,
            'price' => $this->stockPrice,
            'quote_updated_at' => $now,
        ]);

        $this->assertEquals(1, Stock::whereSymbol($this->stockSymbol)->count());
        $this->assertEquals($this->stockPrice, Stock::whereSymbol($this->stockSymbol)->first()->price);

        $stocks->firstOrCreate($this->stockSymbol, [
            'symbol' => $this->stockSymbol,
            'price' => $this->stockPrice + 10,
            'quote_updated_at' => $now->subDay(),
        ]);

        $this->assertEquals(1, Stock::whereSymbol($this->stockSymbol)->count());
        $this->assertEquals($this->stockPrice, Stock::whereSymbol($this->stockSymbol)->first()->price); // Assert price did NOT update
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function testUpdateStock()
    {
        $now = Carbon::now();
        $yesterday = $now->subDay();
        $stocks = $this->getValidStockServiceMock();

        $stock = $stocks->create([
            'symbol' => $this->stockSymbol,
            'price' => $this->stockPrice,
            'quote_updated_at' => $yesterday,
        ]);
        $this->assertEquals(1, Stock::whereSymbol($this->stockSymbol)->count());

        $newStockPrice = $this->stockPrice + 10;
        $stocks->update($this->stockSymbol, [
            'price' => $newStockPrice,
            'quote_updated_at' => $now,
        ]);
        $this->assertEquals(1, Stock::whereSymbol($this->stockSymbol)->count());

        $stock = Stock::whereSymbol($this->stockSymbol)->first();

        $this->assertEquals($newStockPrice, $stock->price);
        $this->assertTrue($now->diffInSeconds($stock->quote_updated_at) < 1);
    }

    /**
     * @return void
     */
    public function testFindStockBySymbol()
    {
        $stocks = $this->getValidStockServiceMock();

        Stock::create([
            'symbol' => $this->stockSymbol,
            'price' => $this->stockPrice,
            'quote_updated_at' => Carbon::now(),
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
}
