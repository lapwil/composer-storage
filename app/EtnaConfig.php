<?php

namespace TestStorage;

use Silex\Application;
use Silex\ServiceProviderInterface;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

use ETNA\Storage as ETNAStorage;

class EtnaConfig implements ServiceProviderInterface
{
    /**
     *
     * @{inherit doc}
     */
    public function register(Application $app)
    {
        $handler = null;
        if ("testing" === $app['application_env']) {
            $path = [
                "/../Tests/Data/dl/activities/IDV-OPTD/003/quest/MyCRD/conf.ini",
                "/../Tests/Functional/features/requests/conf_modified.ini",
                "/../Tests/Functional/features/requests/test.txt"
            ];

            $mock = new MockHandler([
                new Response(200, [], "OK"),
                new Response(404, [], "Not Found"),
                new Response(200, [], file_get_contents(__DIR__ . $path[0])),
                new Response(404, [], "Not Found"),
                new Response(204, [], "No Content"),
                new Response(200, [], file_get_contents(__DIR__ . $path[1])),
                new Response(201, [], "Created"),
                new Response(200, [], file_get_contents(__DIR__ . $path[2])),
                new Response(404, [], "Not Found"),
                new Response(404, [], "Not Found")
            ]);

            $handler = HandlerStack::create($mock);
        }

        $app["file-manager"] = $app->share(function ($app) use ($handler) {
            return new ETNAStorage\NginxStorage($app["dl-url"], $app["dl-url-key"], '%1$s.%2$s.%3$s', $handler);
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
