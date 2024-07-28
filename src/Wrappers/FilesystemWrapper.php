<?php

namespace ComissionCalculator\Wrappers;

class FilesystemWrapper
{
    /**
     * @param string $filename
     * @param string $mode
     * @param bool $use_include_path
     * @param resource|null $context
     * @return false|resource
     */
    public function fileOpen(
        string $filename,
        string $mode,
        bool $use_include_path = false,
        $context = null
    ) {
        return fopen($filename, $mode, $use_include_path, $context);
    }

    /**
     * @param resource $stream
     * @param int|null $length
     * @return false|string
     */
    public function fileGetString($stream, ?int $length = null): false|string
    {
        return fgets($stream, $length);
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
}
