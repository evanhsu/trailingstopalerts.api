<?php

namespace Tests\Unit;

use App\Domain\StockQuote;
use App\Infrastructure\Services\AlphaVantage;
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

    public function setUp()
    {
        parent::setUp();

        $this->alphaVantage = new AlphaVantage('fakeApiKey');
        $this->alphaVantage->setHttpClient($this->getMockGuzzleClient());
    }

    /**
     * Returns a 'pre-recorded' response when any request is sent out.
     * @return Client
     */
    protected function getMockGuzzleClient()
    {
        $status = 200;
        $headers = [
            'Connection' => 'keep-alive',
            'Server' => 'gunicorn/19.7.0',
            'Date' => 'Mon, 12 Mar 2018 03:33:15 GMT',
            'Transfer-Encoding' => 'chunked',
            'X-Frame-Options' => 'SAMEORIGIN',
            'Vary' => 'Cookie',
            'Content-Type' => 'application/json',
            'Allow' => 'GET, HEAD, OPTIONS',
            'Via' => '1.1 vegur',
        ];
        $body = '{
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
                }';

        $mockResponse = new MockHandler([
            new Response($status, $headers, $body)
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
        $quotes = $this->alphaVantage->batchQuote(['MSFT', 'FB']);

        $this->assertTrue($quotes instanceof Collection, 'AlphaVantage must return a Collection');
        $this->assertTrue($quotes->first() instanceof StockQuote, 'AlphaVantage must return a Collection of StockQuote objects');
        $this->assertTrue($quotes->where('symbol', 'MSFT')->first()->price == 96.18, 'AlphaVantage returns MSFT quote with wrong price');
        $this->assertTrue($quotes->where('symbol', 'FB')->first()->price == 185.23, 'AlphaVantage returns FB quote with wrong price');
    }
}
