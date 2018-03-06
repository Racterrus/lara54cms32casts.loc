<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Views\Composers\NavigationComposer; // мое!!! :)

class ComposerServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        view()->composer('front.blog.show', NavigationComposer::class); //см. подробнее в http://laravel.su/docs/5.5/providers
        view()->composer('front.blog.index', NavigationComposer::class);
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
