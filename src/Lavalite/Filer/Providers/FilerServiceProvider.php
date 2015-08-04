<?php namespace Lavalite\Filer\Providers;

use Illuminate\Support\ServiceProvider;

class FilerServiceProvider extends ServiceProvider {

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadViewsFrom(__DIR__.'/../../../../resources/views', 'filer');

        $this->publishes([
            __DIR__.'/../../config/config.php' => config_path('filer.php'),
        ], 'config');

        $this->publishes([
            __DIR__.'/../../../../resources/assets/jquery-file-upload/jquery.uploadfile.min.js' 
            => public_path('/vendor/lavalite/filer/js/jquery.uploadfile.min.js'),

            __DIR__.'/../../../../resources/assets/jquery-file-upload/uploadfile.css' 
            => public_path('/vendor/lavalite/filer/css/uploadfile.css'),

        ], 'public');

        include __DIR__.'/../Http/routes.php';
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('filer', function($app)
        {
            return new \Lavalite\Filer\Filer();
        });

    }


    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array('filer');
    }

}
