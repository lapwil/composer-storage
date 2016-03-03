<?php

namespace TestStorage;

use Silex\Application;
use Silex\ServiceProviderInterface;

/**
 * Configuration principale de l'application
 */
class Config implements ServiceProviderInterface
{
    /**
     * @params string $env
     */
    public function __construct()
    {
        if (true === file_exists(__DIR__ . "/env/testing.php")) {
            require_once __DIR__ . "/env/testing.php";
        }
    }
    /**
     * @{inherit doc}
     */
    public function register(Application $app)
    {
        $this->registerEnvironmentParams($app);
        $app->register(new EtnaConfig());
    }
    /**
     *
     * @{inherit doc}
     */
    public function boot(Application $app)
    {
        return $app;
    }
    /**
     * Set up environmental variables
     *
     * If development environment, set xdebug to display all the things
     *
     * @param Application $app Silex Application
     *
     */
    private function registerEnvironmentParams(Application $app)
    {
        $app['application_name']      = 'test_libutils';
        $app['application_env']       = 'testing';
        $app['application_path']      = realpath(__DIR__ . "/../");
        $app['application_namespace'] = __NAMESPACE__;

        $app["dl-url"]     = getenv("DOWNLOAD_URL");
        $app["dl-url-key"] = getenv("DOWNLOAD_URL_KEY");

        ini_set('display_errors', true);
        ini_set('xdebug.var_display_max_depth', 100);
        ini_set('xdebug.var_display_max_children', 100);
        ini_set('xdebug.var_display_max_data', 100);
        error_reporting(E_ALL);
        $app["debug"] = true;
    }
}
