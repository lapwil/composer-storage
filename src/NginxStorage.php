<?php

namespace ETNA\Storage;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7;
use GuzzleHttp\Promise;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Exception\RequestException;
use Exception;

class NginxStorage extends AbstractStorage
{
    private $url       = null;
    private $client    = null;
    private $secret    = null;
    private $format    = null;
    public  $container = [];

    function __construct($url, $secret, $format = '%1$s.%2$s.%3$s')
    {
        $this->url    = $url;
        $this->secret = $secret;
        $this->format = $format;

        if (empty($this->url)) {
            throw new \InvalidArgumentException("No url provided");
        }

        if (empty($this->secret)) {
            throw new \InvalidArgumentException("No secret provided");
        }

        // On s'assure d'avoir un seul / a la fin de la chaine
        $this->url = rtrim($this->url, "/") . "/";
    }

    protected function getHTTPClient()
    {
        if (null === $this->client) {
            if ("testing" === getenv("APPLICATION_ENV")) {
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

                $handler      = HandlerStack::create($mock);
                $this->client = new Client(
                    [
                        "base_uri" => $this->url,
                        "handler"  => $handler
                    ]
                );
            } else {
                $this->client = new Client(["base_uri" => $this->url]);
            }
        }

        return $this->client;
    }

    // $path = /activities/public/tpotperjfgsl
    protected function forge($file_path, $basepath = '')
    {
        // On s'assure de ne pas avoir de / a la fin de la chaine
        // parce que celui de base est mis dans le constructeur
        // et qu'il ne faut SURTOUT PAS celui de la fin sinon ça ne marche pas...
        $basepath  = trim($basepath, '/');
        $file_path = ltrim($file_path, '/');
        $path      = "{$basepath}/{$file_path}";

        $expire   = time() + 3600; // par défaut, le lien sera valable 1h
        $url      = Psr7\Uri::resolve(Psr7\uri_for($this->url), $path);

        $md5 = base64_encode(md5(sprintf($this->format, $expire, $url->getPath(), $this->secret), true));
        $md5 = strtr($md5, '+/', '-_');
        $md5 = str_replace('=', '', $md5);

        $query = "md5={$md5}&expires={$expire}";

        return $url->withQuery($query);
    }

    public function listFiles($dir, $json, $basepath = '', Callable $callback = null)
    {
        $dir   = trim($dir, '/');
        $json  = trim($json, '/');
        $files = json_decode($this->get("{$dir}/{$json}", $basepath), true);

        // On applique la callback pour filtrer les résultats si on en as une
        if (null !== $callback) {
            $files = array_values(
                array_filter($files, $callback)
            );
        }

        return $files;
    }

    public function get($file_path, $basepath = '')
    {
        $url = $this->forge($file_path, $basepath);
        try {
            $request  = new Request('GET', $url);
            $response = $this->getHTTPClient()->send($request);
        } catch (RequestException $exception) {
            $response = $exception->getResponse();
            return $response->getReasonPhrase();
        }

        return $response->getBody()->getContents();
    }

    public function put($file_path, $stream, $basepath = '')
    {
        $url = $this->forge($file_path, $basepath);

        try {
            $request  = new Request('PUT', $url, [], $stream);
            $response = $this->getHTTPClient()->send($request);
        } catch (RequestException $exception) {
            $response = $exception->getResponse();
            return $response;
        }

        return $response;
    }

    public function putDir($dir_path, $basepath = '')
    {
        $dir_path = trim($dir_path, '/');
        $url      = $this->forge("{$dir_path}/.keep", $basepath);

        try {
            $request  = new Request('PUT', $url, [], '');
            $response = $this->getHTTPClient()->send($request);
        } catch (RequestException $exception) {
            $response = $exception->getResponse();
            return $response;
        }

        return $response;
    }

    public function delete($file_path, $basepath = '')
    {
        $url = $this->forge($file_path, $basepath);

        try {
            $request  = new Request('DELETE', $url);
            $response = $this->getHTTPClient()->send($request);
        } catch (RequestException $exception) {
            $response = $exception->getResponse();
            return $response;
        }

        return $response;
    }

    public function downloadFile($file_path, $basepath = '')
    {
        $url = $this->forge($file_path, $basepath);

        return $url->__toString();
    }
}
