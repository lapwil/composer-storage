<?php

namespace ETNA\Storage;

abstract class AbstractStorage implements StorageInterface
{
    public function getFiles(Array $paths)
    {
        $result = [];
        foreach ($paths as $path) {
            $result[$path] = $this->get($path);
        }
        return $result;
    }

    public function putFiles(Array $files)
    {
        foreach ($files as $path => $stream) {
            $this->put($path, $stream);
        }
    }

    public function deleteFiles(Array $paths)
    {
        foreach ($paths as $path) {
            $this->delete($path);
        }
    }
}
