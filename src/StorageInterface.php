<?php

namespace ETNA\Storage;

interface StorageInterface
{
    public function listFiles($dir, $json, $basepath = '', Callable $callback = null);

    public function get($file_path, $basepath = '');
    public function put($file_path, $stream, $basepath = '');
    public function delete($file_path, $basepath = '');
    public function downloadFile($file_path, $basepath = '');

    public function getFiles(Array $paths);
    public function putFiles(Array $files);
    public function deleteFiles(Array $paths);
}
