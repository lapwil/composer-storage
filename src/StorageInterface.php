<?php

namespace ETNA\Storage;

interface StorageInterface
{
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
    public function listFiles($dir, $json, $basepath = '', callable $callback = null);

    /**
     * Get one file from remote server
     *
     * @param  string $file_path file path
     * @param  string $basepath  base path (activities, etc...)
     *
     * @return string
     */
    public function getFile($file_path, $basepath = '');

    /**
     * Insert one file on remote server
     *
     * @param  string $file_path file path
     * @param  string $stream    file content
     * @param  string $basepath  base path (activities, etc...)
     *
     * @return GuzzleHttp\Psr7\Response
     */
    public function putFile($file_path, $stream, $basepath = '');

    /**
     * Delete one file on remote server
     *
     * @param  string $file_path file path
     * @param  string $basepath  base path (activities, etc...)
     *
     * @return GuzzleHttp\Psr7\Response
     */
    public function deleteFile($file_path, $basepath = '');

    /**
     * Return a string that lets people download the file
     *
     * @param  string $file_path file path
     * @param  string $basepath  base path (activities, etc...)
     *
     * @return string
     */
    public function downloadFile($file_path, $basepath = '');

    /**
     * Get files from remote server
     *
     * @param  array $files array of files path and basepath
     *
     * @return array
     */
    public function getFiles(array $files);

    /**
     * Put files from remote server
     *
     * @param  array $files array of files path and basepath and streams
     *
     * @return array
     */
    public function putFiles(array $files);

    /**
     * Remove files from remote server
     *
     * @param  array $files array of files path and basepath
     *
     * @return array
     */
    public function deleteFiles(array $files);
}
