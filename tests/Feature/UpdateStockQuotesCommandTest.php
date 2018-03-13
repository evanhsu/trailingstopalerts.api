<?php

namespace Tests\Feature;

use App\Console\Commands\UpdateStockQuotes;
use App\Domain\Stock;
use App\Domain\StockQuote;
use App\Events\StockUpdated;
use Carbon\Carbon;
use Illuminate\Support\Facades\Event;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\Console\Tester\CommandTester;

class UpdateStockQuotesCommandTest extends TestCase
{
    use MockeryPHPUnitIntegration, RefreshDatabase;

    /**
     * @var \Mockery\MockInterface
     */
    protected $alphaVantage;

    protected $stockMSFT;
    protected $stockGOOGL;
    protected $stockFB;
    protected $stockAMZN;

    protected $yesterday;
    protected $now;

    protected $command;
    protected $commandTester;

    public function setUp()
    {
        parent::setUp();

        $this->now = Carbon::now();
        $this->yesterday = Carbon::now()->subDay();

        $this->seedStocks();
        $this->setAlphaVantageMockResponse();

        $this->command = new UpdateStockQuotes($this->alphaVantage);
        $this->command->setLaravel($this->app);

        $this->commandTester = new CommandTester($this->command);
    }

    private function setAlphaVantageMockResponse()
    {
        $alphaVantageResponse1 = collect([$this->stockMSFT, $this->stockGOOGL]);
        $alphaVantageResponse2 = collect([$this->stockFB, $this->stockAMZN]);

        $this->alphaVantage = Mockery::mock('App\Infrastructure\Services\AlphaVantage');
        $this->alphaVantage->shouldReceive('batchQuote')
            ->andReturn($alphaVantageResponse1, $alphaVantageResponse2); // 2 valid responses are queued up
    }

    private function seedStocks()
    {
        Stock::create([
            'symbol' => 'MSFT',
            'price' => 10,
            'quote_updated_at' => $this->yesterday,
        ]);

        Stock::create([
            'symbol' => 'GOOGL',
            'price' => 11,
            'quote_updated_at' => $this->yesterday,
        ]);

        Stock::create([
            'symbol' => 'FB',
            'price' => 12,
            'quote_updated_at' => $this->yesterday,
        ]);

        Stock::create([
            'symbol' => 'AMZN',
            'price' => 13,
            'quote_updated_at' => $this->yesterday,
        ]);

        // The following values will be returned from the Mock AlphaVantage API
        $this->stockMSFT = new StockQuote('MSFT', 20, $this->now);
        $this->stockGOOGL = new StockQuote('GOOGL', 21, $this->now);
        $this->stockFB = new StockQuote('FB', 22, $this->now);
        $this->stockAMZN = new StockQuote('AMZN', 13, $this->now);
    }

    public function testAllStocksAreUpdated()
    {
        Event::fake(StockUpdated::class); // ONLY intercept this event type

        $this->assertEquals(10, Stock::whereSymbol('MSFT')->first()->price);
        $this->assertEquals(11, Stock::whereSymbol('GOOGL')->first()->price);
        $this->assertEquals(12, Stock::whereSymbol('FB')->first()->price);
        $this->assertEquals(13, Stock::whereSymbol('AMZN')->first()->price);

        // Run command
        $this->commandTester->execute([
            '--batch-size' => 2,
        ]);

        $this->assertEquals(20, Stock::whereSymbol('MSFT')->first()->price);
        $this->assertEquals(21, Stock::whereSymbol('GOOGL')->first()->price);
        $this->assertEquals(22, Stock::whereSymbol('FB')->first()->price);
        $this->assertEquals(13, Stock::whereSymbol('AMZN')->first()->price); // This price doesn't change

        Event::assertDispatched(StockUpdated::class, 3); // Only 3 out of 4 stocks actually changed price
    }
}
