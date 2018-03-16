<?php

namespace Tests\Unit;

use App\Domain\Stock;
use App\Domain\StockDailySummary;
use App\Domain\StockQuote;
use App\Infrastructure\Services\AlphaVantage;
use Carbon\Carbon;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Collection;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Tests\TestCase;
use GuzzleHttp\Client;

class AlphaVantageTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public $alphaVantage;

    protected $bulkQuoteResponse = [
        'status' => 200,
        'headers' => [
            'Connection' => 'keep-alive',
            'Server' => 'gunicorn/19.7.0',
            'Date' => 'Mon, 12 Mar 2018 03:33:15 GMT',
            'Transfer-Encoding' => 'chunked',
            'X-Frame-Options' => 'SAMEORIGIN',
            'Vary' => 'Cookie',
            'Content-Type' => 'application/json',
            'Allow' => 'GET, HEAD, OPTIONS',
            'Via' => '1.1 vegur',
        ],
        'body' => '{
            "Meta Data": {
                "1. Information": "Batch Stock Market Quotes",
                "2. Notes": "IEX Real-Time Price provided for free by IEX (https://iextrading.com/developer/).",
                "3. Time Zone": "US/Eastern"
            },
            "Stock Quotes": [
                {
                    "1. symbol": "MSFT",
                    "2. price": "96.1800",
                    "3. volume": "--",
                    "4. timestamp": "2018-03-09 16:01:30"
                },
                {
                    "1. symbol": "FB",
                    "2. price": "185.2300",
                    "3. volume": "--",
                    "4. timestamp": "2018-03-09 15:59:59"
                }
            ]
        }'
    ];

    protected $timeSeriesDailyResponse = [
        'status' => 200,
        'headers' => [
            'Connection' => 'keep-alive',
            'Server' => 'gunicorn/19.7.0',
            'Date' => 'Mon, 12 Mar 2018 03:33:15 GMT',
            'Transfer-Encoding' => 'chunked',
            'X-Frame-Options' => 'SAMEORIGIN',
            'Vary' => 'Cookie',
            'Content-Type' => 'application/json',
            'Allow' => 'GET, HEAD, OPTIONS',
            'Via' => '1.1 vegur',

        ],
        'body' => '{
            "Meta Data": {
                "1. Information": "Daily Prices (open, high, low, close) and Volumes",
                "2. Symbol": "MSFT",
                "3. Last Refreshed": "2018-03-15",
                "4. Output Size": "Compact",
                "5. Time Zone": "US/Eastern"
            },
            "Time Series (Daily)": {
                "2018-03-15": {
                    "1. open": "93.5300",
                    "2. high": "94.5800",
                    "3. low": "92.8300",
                    "4. close": "94.1800",
                    "5. volume": "27317790"
                },
                "2018-03-14": {
                    "1. open": "95.1200",
                    "2. high": "95.4100",
                    "3. low": "93.5000",
                    "4. close": "93.8500",
                    "5. volume": "31576898"
                },
                "2018-03-13": {
                    "1. open": "97.0000",
                    "2. high": "97.2400",
                    "3. low": "93.9700",
                    "4. close": "94.4100",
                    "5. volume": "34445391"
                }
            }
        }',
    ];

    public function setUp()
    {
        parent::setUp();

        $this->alphaVantage = new AlphaVantage('fakeApiKey');
    }

    /**
     * @param $response
     * @return Client
     */
    protected function getMockGuzzleClient($response)
    {
        $mockResponse = new MockHandler([
            new Response($response['status'], $response['headers'], $response['body'])
        ]);

        $mockHandler = HandlerStack::create($mockResponse);
        $mockClient = new Client(['handler' => $mockHandler]);

        return $mockClient;
    }

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testBatchQuoteReturnsCorrectCollection()
    {
        $this->alphaVantage->setHttpClient($this->getMockGuzzleClient($this->bulkQuoteResponse));
        $quotes = $this->alphaVantage->batchQuote(['MSFT', 'FB']);

        $this->assertTrue($quotes instanceof Collection, 'AlphaVantage must return a Collection');
        $this->assertTrue($quotes->first() instanceof StockQuote, 'AlphaVantage must return a Collection of StockQuote objects');
        $this->assertTrue($quotes->where('symbol', 'MSFT')->first()->price == 96.18, 'AlphaVantage returns MSFT quote with wrong price');
        $this->assertTrue($quotes->where('symbol', 'FB')->first()->price == 185.23, 'AlphaVantage returns FB quote with wrong price');
    }

    /**
     *
     */
    public function testTimeSeriesDaily()
    {
        $this->alphaVantage->setHttpClient($this->getMockGuzzleClient($this->timeSeriesDailyResponse));
        $quote = $this->alphaVantage->dailyQuote('MSFT');

        $this->assertTrue($quote instanceof Stock, 'AlphaVantage::dailyQuote must return a Stock instance');
        $this->assertTrue($quote->open === 93.53);
        $this->assertTrue($quote->close === 94.18);
        $this->assertTrue($quote->high === 94.58);
        $this->assertTrue($quote->low === 92.83);
        $this->assertTrue(Carbon::parse('2018-03-15')->eq($quote->quote_updated_at));
    }
}
