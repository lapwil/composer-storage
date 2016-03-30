<?php

namespace ETNA\Storage;

use Exception;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7;
use GuzzleHttp\Promise;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

class NginxStorage extends AbstractStorage
{
    private $url    = null;
    private $client = null;
    private $secret = null;
    private $format = null;

    public function __construct($url, $secret, $format = '%1$s.%2$s.%3$s')
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

    /**
     * Get HTTP client
     *
     * @return GuzzleHttp\Client
     */
    protected function getHTTPClient()
    {
        if (null === $this->client) {
            $this->client = new Client(["base_uri" => $this->url]);
        }

        return $this->client;
    }

    // $path = /activities/public/tpotperjfgsl
    protected function forge($file_path, $base_path = "")
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

    /**
     * Get a list of files from remote server
     *
     * @param  string        $dir      folder name
     * @param  string        $json     json file list
     * @param  string        $basepath base path (activities, etc...)
     * @param  null|callable $callback callback applied on the json file list before return
     *
     * @return array
     */
    public function listFiles($dir, $json, $basepath = '', callable $callback = null)
    {
        $dir   = trim($dir, '/');
        $json  = trim($json, '/');
        $files = json_decode($this->getFile("{$dir}/{$json}", $basepath), true);

        // On applique la callback pour filtrer les résultats si on en as une
        if (null !== $callback) {
            $files = array_values(
                array_filter($files, $callback)
            );
        }

        return $files;
    }

    /**
     * Get one file from remote server
     *
     * @param  string $file_path file path
     * @param  string $basepath  base path (activities, etc...)
     *
     * @return string
     */
    public function getFile($file_path, $basepath = '')
    {
        $request  = $this->createRequest($file_path, "GET", $basepath);
        $response = $this->sendRequest($request);

        return $response->getBody()->getContents();
    }

    /**
     * Insert one file on remote server
     *
     * @param  string $file_path file path
     * @param  string $stream    file content
     * @param  string $basepath  base path (activities, etc...)
     *
     * @return GuzzleHttp\Psr7\Response
     */
    public function putFile($file_path, $stream, $basepath = '')
    {
        $request = $this->createRequest($file_path, "PUT", $basepath, $stream);

        return $this->sendRequest($request);
    }

    /**
     * Insert a folder on remote server
     *
     * @param  string $dir_path folder path
     * @param  string $basepath base path (activities, etc...)
     *
     * @return GuzzleHttp\Psr7\Response
     */
    public function putDir($dir_path, $basepath = '')
    {
        $dir_path = trim($dir_path, '/');
        $response = $this->putFile("{$dir_path}/.keep", '', $basepath);

        return $response;
    }

    /**
     * Delete one file on remote server
     *
     * @param  string $file_path file path
     * @param  string $basepath  base path (activities, etc...)
     *
     * @return GuzzleHttp\Psr7\Response
     */
    public function deleteFile($file_path, $basepath = '')
    {
        $request = $this->createRequest($file_path, "DELETE", $basepath);

        return $this->sendRequest($request);
    }

    /**
     * Return a string that lets people download the file
     *
     * @param  string $file_path file path
     * @param  string $basepath  base path (activities, etc...)
     *
     * @return string
     */
    public function downloadFile($file_path, $basepath = '')
    {
        $url = $this->forge($file_path, $basepath);

        return $url->__toString();
    }

    /**
     * Send request to remote server
     *
     * @param  Request $request request
     *
     * @return GuzzleHttp\Psr7\Response
     */
    private function sendRequest(Request $request)
    {
        $response = null;
        try {
            $response = $this->getHTTPClient()->send($request);
        } catch (RequestException $exception) {
            $response = $exception->getResponse();
            throw new \Exception($response->getReasonPhrase(), $response->getStatusCode());
        }

        return $response;
    }

    /**
     * Create a request
     *
     * @param  string $path     file path
     * @param  string $method   http method
     * @param  string $basepath base path (activities, etc...)
     * @param  string $stream   file content
     *
     * @return Request
     */
    private function createRequest($path, $method, $basepath = '', $stream = '')
    {
        $url = $this->forge($path, $basepath);
        switch ($method) {
            case "PUT":
                $request = new Request($method, $url, [], $stream);
                break;
            default:
                $request = new Request($method, $url);
                break;
        }

        return $request;
    }
}
