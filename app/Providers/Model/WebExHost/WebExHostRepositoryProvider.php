<?php

    namespace App\Providers\Model\WebExHost;

    use Illuminate\Support\ServiceProvider;

    /**
     * Class ElasticRepositoryProvider
     *
     * @package App\Providers\Model\Elastic
     */
    class WebExHostRepositoryProvider extends ServiceProvider
    {
        /**
         * Bootstrap the application services.
         *
         * @return void
         */
        public function boot()
        {
            //
        }

        /**
         * Register the application services.
         *
         * @return void
         */
        public function register()
        {
            $this->app->bind(\App\Model\WebExHost\Repository\IWebExHostRepository::class, \App\Model\WebExHost\Repository\WebExHostRepository::class);
        }
    }
