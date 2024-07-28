<?php

namespace CommissionApp\Wrappers;

class FilesystemWrapper
{
    /**
     * @param string $filename
     * @param string $mode
     * @return false|resource
     */
    public function fileOpen(string $filename, string $mode)
    {
        return fopen($filename, $mode);
    }

    /**
     * @param resource $stream
     * @return false|string
     */
    public function fileGetString($stream): false|string
    {
        return fgets($stream);
    }

    /**
     * @param string $filename
     * @return bool
     */
    public function fileExists(string $filename): bool
    {
        return file_exists($filename);
    }

    /**
     * @param string $filename
     * @return bool
     */
    public function isFile(string $filename): bool
    {
        return is_file($filename);
    }

    /**
     * @param resource $stream
     * @return bool
     */
    public function fileClose($stream): bool
    {
        return fclose($stream);
    }

    /**
     * @param string $filename
     * @return false|string
     */
    public function fileGetContents(string $filename): false|string
    {
        return file_get_contents($filename);
    }
}
