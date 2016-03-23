<?php
use Behat\Behat\Context\BehatContext;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Exception\RequestException;

use ETNA\FeatureContext as EtnaFeatureContext;
use ETNA\Storage as ETNAStorage;

class FeatureContext extends BehatContext
{
    use EtnaFeatureContext\Coverage;
    use EtnaFeatureContext\Check;
    use EtnaFeatureContext\SilexApplication;
    use EtnaFeatureContext\FixedTime;
    use EtnaFeatureContext\TimeProfiler;
    use EtnaFeatureContext\setUpScenarioDirectories;

    static private $vhosts = ["/test-behat"];
    static private $_parameters;
    static private $server;
    private $data;
    private $exception;

    /**
     * Initializes context.
     * Every scenario gets it's own context object.
     *
     * @param array $parameters context parameters (set them up through behat.yml)
     */
    public function __construct(array $parameters)
    {
        self::$_parameters = $parameters;
        $this->base_url    = "http://localhost:8080";
        $this->request     = [
            "headers" => [],
            "cookies" => [],
            "files"   => [],
        ];
    }

    /**
     * @BeforeSuite
     */
    public static function startServer()
    {
        $conf = __DIR__ . "/nginx.conf";
        $tmp  = __DIR__ . "/../../Data/dl/tmp";
        exec("nginx -c {$conf}");
        self::deleteFiles();
    }

    /**
     * @AfterSuite
     */
    public static function stopServer()
    {
        exec("nginx -s stop");
    }

    /**
     * @BeforeScenario @filer
     */
    public static function addFiles()
    {
        $path      = __DIR__ . "/../../Data/dl/activities";
        $dest      = __DIR__ . "/../../Data/dl/tmp";
        $directory = new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS);
        $iterator  = new RecursiveIteratorIterator($directory, RecursiveIteratorIterator::SELF_FIRST);
        foreach ($iterator as $item) {
            if ($item->isDir()) {
                mkdir($dest . DIRECTORY_SEPARATOR . $iterator->getSubPathName());
            } else {
                copy($item, $dest . DIRECTORY_SEPARATOR . $iterator->getSubPathName());
            }
        }
    }

    /**
     * @AfterScenario @filer
     */
    public static function deleteFiles()
    {
        $dest = __DIR__ . "/../../Data/dl/tmp";
        exec("rm -r {$dest}");
        mkdir($dest, 0755);
    }

    /**
     * Error handler
     *
     * @param array $data   datas to test
     * @param array $errors errors
     */
    private function handleErrors($data, $errors)
    {
        if ($nb_err = count($errors)) {
            echo json_encode($data, JSON_PRETTY_PRINT);
            throw new Exception("{$nb_err} errors :\n" . implode("\n", $errors));
        }
    }

    /**
     * @Given /^je veux récupérer le contenu du fichier "([^"]*)" situé dans "([^"]*)"$/
     */
    public function jeVeuxRecupererLeContenuDuFichierSitueDans($path, $basepath)
    {
        $app = self::$silex_app;
        try {
            $this->data = $app["file-manager"]->get($path, $basepath);
        } catch (\Exception $exception) {
            $this->exception = $exception->getMessage();
        }
    }

    /**
     * @Then /^le résultat devrait être identique au fichier "(.*)"$/
     */
    public function leResultatDevraitRessemblerAuFichier($file)
    {
        $file    = realpath($this->results_path . "/" . $file);
        $content = file_get_contents($file);

        $this->check($content, $this->data, "result", $errors);
        $this->handleErrors($this->data, $errors);
    }

    /**
     * @Then /^la réponse devrait être "([^"]*)"$/
     */
    public function laReponseDevraitEtre($error_message)
    {
        $this->check($error_message, $this->data, "result", $errors);
        $this->handleErrors($this->data, $errors);
    }

    /**
     * @Given /^je devrais avoir une exception "([^"]*)"$/
     */
    public function jeDevraisAvoirUneException($exception)
    {
        if ($exception != $this->exception) {
            throw new Exception("Expected: '{$exception}'; got: '{$this->exception}'");
        }
    }

    /**
     * @Given /^je veux remplacer le fichier "([^"]*)" situé dans "([^"]*)" par le fichier "([^"]*)"$/
     * @Given /^je veux ajouter le fichier "([^"]*)" situé dans "([^"]*)" avec le fichier "([^"]*)"$/
     */
    public function jeVeuxRemplacerLeFichierSitueDansParLeFichier($path, $basepath, $file)
    {
        $app     = self::$silex_app;
        $file    = realpath($this->results_path . "/" . $file);
        $content = file_get_contents($file);
        try {
            $this->data = $app["file-manager"]->put($path, $content, $basepath)->getReasonPhrase();
        } catch (\Exception $exception) {
            $this->exception = $exception->getMessage();
        }
    }

    /**
     * @Given /^je veux supprimer le fichier "([^"]*)" situé dans "([^"]*)"$/
     */
    public function jeVeuxSupprimerLeFichierSitueDans($path, $basepath)
    {
        $app = self::$silex_app;
        try {
            $this->data = $app["file-manager"]->delete($path, $basepath)->getReasonPhrase();
        } catch (\Exception $exception) {
            $this->exception = $exception->getMessage();
        }
    }
}
