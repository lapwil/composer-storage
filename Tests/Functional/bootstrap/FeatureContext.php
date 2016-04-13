<?php
use Behat\Behat\Context\BehatContext;

use ETNA\FeatureContext as EtnaFeatureContext;

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
    private $app;
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

        $this->app = self::$silex_app;
    }

    /**
     * @BeforeSuite
     */
    public static function startServer()
    {
        $prefix = realpath(__DIR__ . "/../..");
        $conf   = __DIR__ . "/nginx.conf";
        exec("nginx -c {$conf} -p ${prefix}/");
        echo("Nginx server started\n");
        self::deleteFiles();
    }

    /**
     * @AfterSuite
     */
    public static function stopServer()
    {
        exec("nginx -s stop");
        echo("\nNginx server stopped\n");
    }

    /**
     * @BeforeScenario
     */
    public static function addFiles()
    {
        $path      = realpath(__DIR__ . "/../../Data/dl/activities");
        $dest      = realpath(__DIR__ . "/../../Data/dl/tmp");
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
     * @AfterScenario
     */
    public static function deleteFiles()
    {
        $dest = realpath(__DIR__ . "/../../Data/dl/tmp");
        chmod("{$dest}", 0755);
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
        try {
            $this->data = $this->app["file-manager"]->getFile($path, $basepath);
        } catch (\Exception $exception) {
            $this->exception = $exception->getMessage();
        }
    }

    /**
     * @Given /^je veux récupérer le contenu des fichiers listés dans "([^"]*)"$/
     */
    public function jeVeuxRecupererLeContenuDesFichiersListesDansSitueDans($json)
    {
        $json  = realpath($this->requests_path . $json);
        $files = json_decode(file_get_contents($json), true);
        if (null === $files) {
            throw new Exception("json_decode error");
        }

        try {
            $this->data = $this->app["file-manager"]->getFiles($files);
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
        try {
            $this->data = $this->app["file-manager"]->downloadFile($path, $basepath);
        } catch (\Exception $exception) {
            $this->exception = $exception->getMessage();
        }
    }

    /**
     * @Given /^je veux récupérer la liste des fichiers dans "([^"]*)" contenue dans "([^"]*)" situé dans "([^"]*)"$/
     */
    public function jeVeuxRecupererLaListeDesFichiersDansContenuDansSitueDans($path, $json, $basepath)
    {
        try {
            $this->data = $this->app["file-manager"]->listFiles($path, $json, $basepath);
            $this->data = json_decode(json_encode($this->data));
        } catch (\Exception $exception) {
            $this->exception = $exception->getMessage();
        }
    }

    /**
     * @Given /^je récupère la liste dans "([^"]*)" contenue dans "([^"]*)" situé dans "([^"]*)" filtré avec "([^"]*)"$/
     */
    public function jeRecupereLaListeDansContenueDansSitueDansFiltreAvec($path, $json, $basepath, $filter_path)
    {
        $callback = function($file) use ($filter_path) {
            return preg_match($filter_path, $file["path"]);
        };
        try {
            $this->data = $this->app["file-manager"]->listFiles($path, $json, $basepath, $callback);
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
        $file    = realpath($this->requests_path . $file);
        $content = file_get_contents($file);
        try {
            $this->data = $this->app["file-manager"]->putFile($path, $content, $basepath)->getReasonPhrase();
        } catch (\Exception $exception) {
            $this->exception = $exception->getMessage();
        }
    }

    /**
     * @Given /^je veux ajouter la liste de fichiers contenue dans "([^"]*)"$/
     * @Given /^je veux modifier la liste de fichiers contenue dans "([^"]*)"$/
     */
    public function jeVeuxModifierLaListeDeFichiersContenuDans($json)
    {
        $json  = realpath($this->requests_path . $json);
        $files = json_decode(file_get_contents($json), true);
        if (null === $files) {
            throw new Exception("json_decode error");
        }

        try {
            $this->data = $this->app["file-manager"]->putFiles($files);
        } catch (\Exception $exception) {
            $this->exception = $exception->getMessage();
        }
    }

    /**
     * @Given /^je veux ajouter le répertoire "([^"]*)" situé dans "([^"]*)"$/
     */
    public function jeVeuxAjouterLeRepertoireSitueDans($path, $basepath)
    {
        try {
            $this->data = $this->app["file-manager"]->putDir($path, $basepath)->getReasonPhrase();
        } catch (\Exception $exception) {
            $this->exception = $exception->getMessage();
        }
    }

    /**
     * @Given /^je veux supprimer le fichier "([^"]*)" situé dans "([^"]*)"$/
     */
    public function jeVeuxSupprimerLeFichierSitueDans($path, $basepath)
    {
        try {
            $this->data = $this->app["file-manager"]->deleteFile($path, $basepath)->getReasonPhrase();
        } catch (\Exception $exception) {
            $this->exception = $exception->getMessage();
        }
    }

    /**
     * @Given /^je veux supprimer la liste de fichiers contenue dans "([^"]*)"$/
     */
    public function jeVeuxSupprimerLaListeDeFichiersContenuDans($json)
    {
        $json  = realpath($this->requests_path . $json);
        $files = json_decode(file_get_contents($json), true);
        if (null === $files) {
            throw new Exception("json_decode error");
        }

        try {
            $this->data = $this->app["file-manager"]->deleteFiles($files);
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
     * @param string $string
     */
    public function leResultatDevraitRessemblerAuJsonSuivant($string)
    {
        $result = json_decode($string);
        if (null === $result) {
            throw new Exception("json_decode error");
        }

        $this->check($result, $this->data, "result", $errors);
        $this->handleErrors($this->data, $errors);
    }

    /**
     * @Then /^le résultat devrait être "([^"]*)"$/
     */
    public function leResultatDevraitEtre($error_message)
    {
        $this->check($error_message, $this->data, "result", $errors);
        $this->handleErrors($this->data, $errors);
    }

    /**
     * @Given /^je devrais avoir une exception "([^"]*)"$/
     */
    public function jeDevraisAvoirUneException($exception)
    {
        if ($exception !== $this->exception) {
            throw new Exception("Expected: '{$exception}'; got: '{$this->exception}'");
        }
    }

    /**
     * @Given /^je veux ajouter la liste de répertoire contenu dans "([^"]*)"$/
     */
    public function jeVeuxAjouterLaListeDeRepertoireContenuDans($json)
    {
        $json  = realpath($this->requests_path . $json);
        $files = json_decode(file_get_contents($json), true);
        if (null === $files) {
            throw new Exception("json_decode error");
        }

        try {
            $this->data = $this->app["file-manager"]->putFolders($files);
        } catch (\Exception $exception) {
            $this->exception = $exception->getMessage();
        }

        foreach ($this->data as &$data) {
            $data = $data->getReasonPhrase();
        }
    }

    /**
     * @Then /^les résultats devraient être "([^"]*)"$/
     */
    public function lesResultatsDevraientEtre($error_message)
    {
        foreach ($this->data as $data) {
            $this->check($error_message, $data, "result", $errors);
            $this->handleErrors($data, $errors);
        }
    }
}
