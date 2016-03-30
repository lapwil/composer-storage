<?php

namespace ETNA\Storage;

abstract class AbstractStorage implements StorageInterface
{
    /**
     * Get files from remote server
     *
     * @param  array $files array of files path and basepath
     *
     * @return array
     */
    public function getFiles(array $files)
    {
        $result = [];
        foreach ($files as $file) {
            $result[$file["path"]] = $this->getFile($file["path"], $file["basepath"]);
        }
        return $result;
    }

    /**
     * Put files from remote server
     *
     * @param  array $files array of files path and basepath and streams
     */
    public function putFiles(array $files)
    {
        foreach ($files as $file) {
            $this->putFile($file["path"], $file["stream"], $file["basepath"]);
        }
    }

    /**
     * Remove files from remote server
     *
     * @param  array $files array of files path and basepath
     */
    public function deleteFiles(array $files)
    {
        foreach ($files as $file) {
            $this->deleteFile($file["path"], $file["basepath"]);
        }
    }
}
