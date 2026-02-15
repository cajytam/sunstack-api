<?php

namespace App\Factory\File;

class FileFactory
{
    static function createDir(string $dirname): void
    {
        if (false === file_exists($dirname)) {
            mkdir($dirname, 0777, true);
        } else if (false === is_writable($dirname)) {
            chmod($dirname, 0777);
        }
    }
}
