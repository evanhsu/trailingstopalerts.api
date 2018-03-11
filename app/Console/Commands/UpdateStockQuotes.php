<?php

namespace App\Console\Commands;

use App\Domain\Stock;
use App\Infrastructure\Services\AlphaVantage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class UpdateStockQuotes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'quotes:update 
                            {--no-triggers : Prevent events from firing during this update (StopAlerts will not be updated & no notifications will be sent)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get the latest stock price from the AlphaVantage API for each Stock in the database.';

    /**
     * @var AlphaVantage $client
     */
    protected $client;

    /**
     * Create a new command instance.
     *
     * @param AlphaVantage $alphaVantage
     */
    public function __construct(AlphaVantage $alphaVantage)
    {
        parent::__construct();
        $this->client = $alphaVantage;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        Log::info('Running UpdateStockQuotes command...');

        $groupedStocks = Stock::all()->pluck('symbol')->chunk(100);
        $quotes = collect([]);

        $groupedStocks->each(function($groupOfStocks) use (&$quotes) {
            $quotes->push($this->client->batchQuote($groupOfStocks));
        });

        if($this->option('no-triggers')) {
            Stock::setFIREEVENTS(false);
        }

        $quotes->flatten()->each(function($stockQuote) {
            $stock = Stock::find($stockQuote->symbol);
            $stock->update($stockQuote->toArray());
        });

        if($this->option('no-triggers')) {
            Stock::setFIREEVENTS(true);
        }

        Log::info('UpdateStockQuotes command completed: '.$quotes->flatten()->count().' quotes retrieved.');

        return true;
    }
}
