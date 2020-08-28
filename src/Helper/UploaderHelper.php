<?php

namespace App\Helper;

use Symfony\Component\HttpFoundation\File\File;

class UploaderHelper
{
    /**
     * @var String
     */
    private $uploadsPath;

    public function __construct(string $uploadsPath)
    {
        $this->uploadsPath = $uploadsPath;
    }

    /**
     * Handle uploading image file and move to proper directory.
     */
    public function uploadFile(File $file, string $type, string $folderName): string
    {
        $destination = $this->uploadsPath.$type.'/'.$folderName;
        $newFileName = md5(uniqid()).'.'.$file->guessExtension();
        $file->move($destination, $newFileName);

        return $newFileName;
    }
}
