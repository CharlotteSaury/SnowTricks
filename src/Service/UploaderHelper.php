<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class UploaderHelper
{
    private $uploadsPath;

    public function __construct(string $uploadsPath)
    {
        $this->uploadsPath = $uploadsPath;
    }
    public function uploadFile(UploadedFile $uploadedFile): string
    {
        $destination = $this->uploadsPath;
        $newFileName =  md5(uniqid()) . '.' . $uploadedFile->guessExtension();
        $uploadedFile->move($destination, $newFileName);
        return $newFileName;
    }
}