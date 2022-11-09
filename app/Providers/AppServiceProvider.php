<?php

namespace App\Providers;

use App\Service\ShopifyMultipass;
use App\Service\ShopifyStorefrontApi;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('shopifyStorefrontApi', function (){
            return new ShopifyStorefrontApi();
        });
        $this->app->bind('ShopifyMultipass', function(){
            return new ShopifyMultipass();
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
