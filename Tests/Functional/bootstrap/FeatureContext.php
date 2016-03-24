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
        chmod("{$dest}", 0755);
        exec("rm -r {$dest}");
        mkdir($dest, 0755);
    }

    /**
     * @BeforeScenario @forbidden
     */
    public static function setErrorRights()
    {
        $tmp = __DIR__ . "/../../Data/dl/tmp";
        chmod("{$tmp}", 0644);
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
     * @Given /^je veux récupérer le contenu des fichiers lister dans "([^"]*)"$/
     */
    public function jeVeuxRecupererLeContenuDesFichiersListerDansSitueDans($json)
    {
        $app   = self::$silex_app;
        $json  = realpath($this->requests_path . $json);
        $files = json_decode(file_get_contents($json), true);
        if ($files === null) {
            throw new Exception("json_decode error");
        }

        try {
            $this->data = $app["file-manager"]->getFiles($files);
            $this->data = json_decode(json_encode($this->data));
        } catch (\Exception $exception) {
            $this->exception = $exception->getMessage();
        }
    }

    /**
     * @Given /^je veux télécharger le fichier "([^"]*)" situé dans "([^"]*)"$/
     */
    public function jeVeuxTelechargerLeFichierSitueDans($path, $basepath)
    {
        $app = self::$silex_app;
        try {
            $this->data = $app["file-manager"]->downloadFile($path, $basepath);
        } catch (\Exception $exception) {
            $this->exception = $exception->getMessage();
        }
    }

    /**
     * @Given /^je veux récupérer la liste des fichiers dans "([^"]*)" contenu dans "([^"]*)" situé dans "([^"]*)"$/
     */
    public function jeVeuxRecupererLaListeDesFichiersDansContenuDansSitueDans($path, $json, $basepath)
    {
        $app = self::$silex_app;
        try {
            $this->data = $app["file-manager"]->listFiles($path, $json, $basepath);
            $this->data = json_decode(json_encode($this->data));
        } catch (\Exception $exception) {
            $this->exception = $exception->getMessage();
        }
    }

    /**
     * @Given /^je veux récupérer la liste des fichiers de "([^"]*)" dans "([^"]*)" contenu dans "([^"]*)" situé dans "([^"]*)"$/
     */
    public function jeVeuxRecupererLaListeDesFichiersDeDansContenuDansSitueDans($filter_path, $path, $json, $basepath)
    {
        $app      = self::$silex_app;
        $callback = function($file) use ($filter_path) {
            return preg_match("#.*\/{$filter_path}\/.*#", $file["path"]);
        };
        try {
            $this->data = $app["file-manager"]->listFiles($path, $json, $basepath, $callback);
            $this->data = json_decode(json_encode($this->data));
        } catch (\Exception $exception) {
            $this->exception = $exception->getMessage();
        }
    }

    /**
     * @Given /^je veux remplacer le fichier "([^"]*)" situé dans "([^"]*)" par le fichier "([^"]*)"$/
     * @Given /^je veux ajouter le fichier "([^"]*)" situé dans "([^"]*)" avec le fichier "([^"]*)"$/
     */
    public function jeVeuxRemplacerLeFichierSitueDansParLeFichier($path, $basepath, $file)
    {
        $app     = self::$silex_app;
        $file    = realpath($this->requests_path . $file);
        $content = file_get_contents($file);
        try {
            $this->data = $app["file-manager"]->put($path, $content, $basepath)->getReasonPhrase();
        } catch (\Exception $exception) {
            $this->exception = $exception->getMessage();
        }
    }

    /**
     * @Given /^je veux ajouter la liste de fichiers contenu dans "([^"]*)"$/
     * @Given /^je veux modifier la liste de fichiers contenu dans "([^"]*)"$/
     */
    public function jeVeuxModifierLaListeDeFichiersContenuDans($json)
    {
        $app   = self::$silex_app;
        $json  = realpath($this->requests_path . $json);
        $files = json_decode(file_get_contents($json), true);
        if ($files === null) {
            throw new Exception("json_decode error");
        }

        try {
            $this->data = $app["file-manager"]->putFiles($files);
        } catch (\Exception $exception) {
            $this->exception = $exception->getMessage();
        }
    }

    /**
     * @Given /^je veux ajouter le répertoire "([^"]*)" situé dans "([^"]*)"$/
     */
    public function jeVeuxAjouterLeRepertoireSitueDans($path, $basepath)
    {
        $app = self::$silex_app;
        try {
            $this->data = $app["file-manager"]->putDir($path, $basepath)->getReasonPhrase();
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

    /**
     * @Given /^je veux supprimer la liste de fichiers contenu dans "([^"]*)"$/
     */
    public function jeVeuxSupprimerLaListeDeFichiersContenuDans($json)
    {
        $app   = self::$silex_app;
        $json  = realpath($this->requests_path . $json);
        $files = json_decode(file_get_contents($json), true);
        if ($files === null) {
            throw new Exception("json_decode error");
        }

        try {
            $this->data = $app["file-manager"]->deleteFiles($files);
        } catch (\Exception $exception) {
            $this->exception = $exception->getMessage();
        }
    }


    /**
     * @Then /^le résultat devrait être identique au fichier "(.*)"$/
     */
    public function leResultatDevraitRessemblerAuFichier($file)
    {
        $file = realpath($this->results_path . "/" . $file);
        $this->leResultatDevraitRessemblerAuJsonSuivant(file_get_contents($file));
    }

    /**
     * @Then /^le résultat devrait être identique à "(.*)"$/
     * @Then /^le résultat devrait être identique au JSON suivant :$/
     * @Then /^le résultat devrait ressembler au JSON suivant :$/
     */
    public function leResultatDevraitRessemblerAuJsonSuivant($string)
    {
        $result = json_decode($string);
        if ($result === null) {
            throw new Exception("json_decode error");
        }

        $this->check($result, $this->data, "result", $errors);
        $this->handleErrors($this->data, $errors);
    }

    /**
     * @Then /^le résultat devrait être "([^"]*)"$/
     */
    public function laResultatDevraitEtre($error_message)
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
}
