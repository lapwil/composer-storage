<?php

namespace ETNA\Storage;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7;
use GuzzleHttp\Promise;
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
            $this->client = new Client(["base_uri" => $this->url]);
        }

        return $this->client;
    }

    // $path = /activities/public/tpotperjfgsl
    protected function forge($file_path, $base_path = '')
    {
        // On s'assure de ne pas avoir de / a la fin de la chaine
        // parce que celui de base est mis dans le constructeur
        // et qu'il ne faut SURTOUT PAS celui de la fin sinon ça ne marche pas...
        $base_path = trim($base_path, '/');
        $file_path = trim($file_path, '/');
        $path      = "{$base_path}/{$file_path}";

        $expire = time() + 3600; // par défaut, le lien sera valable 1h
        $url    = Psr7\Uri::resolve(Psr7\uri_for($this->url), $path);

        $md5 = base64_encode(md5(sprintf($this->format, $expire, urldecode($url->getPath()), $this->secret), true));
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
            throw new \Exception($response->getReasonPhrase(), $response->getStatusCode());
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
            throw new \Exception($response->getReasonPhrase(), $response->getStatusCode());
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
            throw new \Exception($response->getReasonPhrase(), $response->getStatusCode());
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
            throw new \Exception($response->getReasonPhrase(), $response->getStatusCode());
        }

        return $response;
    }

    public function downloadFile($file_path, $basepath = '')
    {
        $url = $this->forge($file_path, $basepath);

        return $url->__toString();
    }
}
