<?php

namespace ETNA\Storage;

interface StorageInterface
{
    public function listFiles($dir, $json, $basepath = '', callable $callback = null);

    public function get($file_path, $basepath = '');
    public function put($file_path, $stream, $basepath = '');
    public function delete($file_path, $basepath = '');
    public function downloadFile($file_path, $basepath = '');

    public function getFiles(array $files);
    public function putFiles(array $files);
    public function deleteFiles(array $files);
}
