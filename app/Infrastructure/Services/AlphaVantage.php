<?php

namespace App\Infrastructure\Services;

use App\Domain\Stock;
use App\Domain\StockQuote;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class AlphaVantage
{
    /**
     * @var string
     */
    protected $apiKey;

    /**
     * @var \GuzzleHttp\Client
     */
    protected $client;

    /**
     * AlphaVantage constructor.
     * @param $apiKey
     */
    public function __construct($apiKey)
    {
        $this->apiKey = $apiKey;
        $this->client = new Client([
            'base_uri' => 'https://www.alphavantage.co/query/',
            'http_errors' => true,
        ]);
    }

    /**
     * Change the Guzzle client used to make API calls (useful for passing in a Mock Guzzle client)
     * @param Client $client
     */
    public function setHttpClient(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Takes an array of stock symbols OR a single stock symbol as a string.
     * Returns a Collection of StockQuote objects
     *
     * @param array|Collection|string $symbols
     * @return Collection
     * @throws \Exception
     */
    public function batchQuote($symbols)
    {
        if (empty($symbols)) {
            return null;
        }

        if (gettype($symbols) == 'string') {
            $symbols = array($symbols);
        }

        if ($symbols instanceof Collection) {
            $symbols = $symbols->all();
        }

        $function = 'BATCH_STOCK_QUOTES';
        $symbolString = implode(',', $symbols);

        $response = $this->client->request('GET', '', [
            'query' => [
                'function' => $function,
                'symbols' => $symbolString,
                'datatype' => 'json',
                'apikey' => $this->apiKey,
            ],
        ]);

        if ($response->getStatusCode() !== 200) {
            throw new \Exception($response->getReasonPhrase() . ' - ' . $response->getBody());
        }

        $body = json_decode($response->getBody());
        $timezone = $body->{'Meta Data'}->{'3. Time Zone'};
        $quotes = collect($body->{'Stock Quotes'})
            ->transform(function ($quote) use ($timezone) {
                return new StockQuote(
                    strtoupper($quote->{'1. symbol'}),
                    $quote->{'2. price'},
                    $quote->{'4. timestamp'},
                    $timezone
                );
            });

        return $quotes;
    }

    public function dailyQuote($symbol, $outputSize = 'compact')
    {
        if (empty($symbol)) {
            return null;
        }

        $function = 'TIME_SERIES_DAILY';

        $response = $this->client->request('GET', '', [
            'query' => [
                'function' => $function,
                'symbol' => $symbol,
                'datatype' => 'json',
                'output_size' => $outputSize,
                'apikey' => $this->apiKey,
            ],
        ]);

        if ($response->getStatusCode() !== 200) {
            throw new \Exception($response->getReasonPhrase() . ' - ' . $response->getBody());
        }

        $body = json_decode($response->getBody());

        if(property_exists($body, "Error Message")) {
            throw new \Exception($body->{'Error Message'});
        }

        $timezone = $body->{'Meta Data'}->{'5. Time Zone'};
        $mostRecentQuoteDate = $body->{'Meta Data'}->{'3. Last Refreshed'};
        $mostRecentQuote = $body->{'Time Series (Daily)'}->{$mostRecentQuoteDate};

        $stock = new Stock([
            'symbol' => strtoupper($symbol),
            'open' => $mostRecentQuote->{'1. open'},
            'high' => $mostRecentQuote->{'2. high'},
            'low' => $mostRecentQuote->{'3. low'},
            'close' => $mostRecentQuote->{'4. close'},
            'quote_updated_at' => Carbon::parse($mostRecentQuoteDate),
        ]);

        return $stock;
    }
}

