<?php
/*
 * File: ServiceProvider.php
 * Project: camelcasetechs/vansosms
 * File Created: Wednesday, 18th December 2019 9:21:32 am
 * Author: Temitayo Bodunrin (temitayo@brandnaware.com)
 * -----
 * Last Modified: Wednesday, 18th December 2019 9:26:59 am
 * Modified By: Temitayo Bodunrin (temitayo@brandnaware.com)
 * -----
 * Copyright 2019, Brandnaware Nigeria
 */

namespace CamelCase\VansoSMS;

use \Illuminate\Support\ServiceProvider as BaseProvider;

class ServiceProvider extends BaseProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/config/config.php', 'vanso-sms'
        );
    }

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/config/config.php' => config_path('vanso-sms.php'),
        ], 'config');
    }
}
