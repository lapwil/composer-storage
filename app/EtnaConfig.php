<?php

namespace TestStorage;

use Silex\Application;
use Silex\ServiceProviderInterface;

use ETNA\Storage as ETNAStorage;

class EtnaConfig implements ServiceProviderInterface
{
    /**
     *
     * @{inherit doc}
     */
    public function register(Application $app)
    {
        $app["file-manager"] = $app->share(function ($app) {
            return new ETNAStorage\NginxStorage($app["dl-url"], $app["dl-url-key"]);
        });
    }
    /**
     *
     * @{inherit doc}
     */
    public function boot(Application $app)
    {
        return $app;
    }
}
