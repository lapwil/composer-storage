<?php

class ServerContext
{
    /**
     * Pid for the web server
     *
     * @var int
     */
    private static $pid;
    private static $config;

    /**
     * Start the built in httpd
     *
     * @param string $host          The hostname to use
     * @param int    $port          The port to use
     * @param string $document_root The document root
     *
     * @return array Output from exec
     */
    public function startBuiltInHttpd($host, $port, $document_root)
    {
        self::$config = [
            "host"          => $host,
            "port"          => $port,
            "document_root" => $document_root
        ];

        // Build the command
        $command = sprintf(
            'php -S %s:%d -t %s >/dev/null 2>&1 & echo $!',
            $host,
            $port,
            $document_root
        );

        $output = [];
        exec($command, $output);
        echo "Server started : {$host}:{$port} {$document_root}";
        self::$pid = (int) $output[0];

        return $output;
    }

    /**
     * Kill the httpd process if it has been started when the tests have finished
     */
    public function tearDown()
    {
        $host          = self::$config["host"];
        $port          = self::$config["port"];
        $document_root = self::$config["document_root"];

        echo "\nKill Server : {$host}:{$port} {$document_root}\n";

        if (self::$pid) {
            self::killProcess(self::$pid);
        }
    }

    /**
     * Kill a process
     *
     * @param int $pid
     */
    private static function killProcess($pid)
    {
        exec('kill ' . (int) $pid);
    }

}
