<?php

namespace bitbuyAT\Bitstamp;

use GuzzleHttp\Client as HttpClient;
use Illuminate\Support\ServiceProvider;

class BitstampServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function boot()
    {
        $this->registerClient();
    }

    /**
     * Register services.
     */
    public function register()
    {
        $configPath = __DIR__.'/../config/bitstamp.php';
        $this->mergeConfigFrom($configPath, 'bitstamp');
    }

    protected function registerClient(): void
    {
        $this->app->singleton(Contracts\Client::class, function () {
            $config = $this->app->make('config')->get('bitstamp', []);

            return new Client(
                new HttpClient(),
                $config['key'] ?? null,
                $config['secret'] ?? null,
                $config['customer_id'] ?? null
            );
        });
    }
}
