<?php

namespace App\Providers;

use App\Infrastructure\Services\AlphaVantage;
use Illuminate\Support\ServiceProvider;
use Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->singleton(AlphaVantage::class, function () {
            $apiKey = env('ALPHA_VANTAGE_API_KEY');

            if (empty($apiKey)) {
                throw new \Exception('No API key provided for the AlphaVantage API');
            }

            return new AlphaVantage($apiKey);
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if ($this->app->environment() !== 'production') {
            $this->app->register(IdeHelperServiceProvider::class);
        }
    }
}
