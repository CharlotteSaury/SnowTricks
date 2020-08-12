<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\File;

class UploaderHelper
{
    private $uploadsPath;

    public function __construct(string $uploadsPath)
    {
        $this->uploadsPath = $uploadsPath;
    }
    public function uploadFile(File $file, string $folderName): string
    {
        $destination = $this->uploadsPath . $folderName;
        $newFileName =  md5(uniqid()) . '.' . $file->guessExtension();
        $file->move($destination, $newFileName);
        return $newFileName;
    }
}