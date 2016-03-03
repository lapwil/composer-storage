<?php

namespace ETNA\Storage;

abstract class AbstractStorage implements StorageInterface
{
    public function getFiles(array $files)
    {
        $result = [];
        foreach ($files as $file) {
            $result[$file["path"]] = $this->get($file["path"], $file["basepath"]);
        }
        return $result;
    }

    public function putFiles(array $files)
    {
        foreach ($files as $file) {
            $this->put($file["path"], $file["stream"], $file["basepath"]);
        }
    }

    public function deleteFiles(array $files)
    {
        foreach ($files as $file) {
            $this->delete($file["path"], $file["basepath"]);
        }
    }
}
