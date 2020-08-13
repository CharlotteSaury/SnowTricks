<?php

namespace App\Service;

use phpDocumentor\Reflection\Types\Boolean;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;

class ImageFileDeletor
{
    private $trickDirectory;

    private $userDirectory;

    private $fileSystem;

    public function __construct(string $trickDirectory, string $userDirectory, Filesystem $fileSystem)
    {
        $this->trickDirectory = $trickDirectory;
        $this->userDirectory = $userDirectory;
        $this->fileSystem = $fileSystem;
    }

    public function deleteFile(string $type, $id, array $data, bool $bool = null)
    {
        if ($type == 'trick') {
            $directory = $this->trickDirectory . $id;
        } elseif ($type == 'user') {
            $directory = $this->userDirectory . $id;
        }
        if ($directory) {
                if (opendir($directory)) {
                    foreach (scandir($directory) as $file) {
                        if ($file != '.' && $file != '..') {
                            if ($bool) {
                                if (in_array($file, $data)) {
                                    $this->fileSystem->remove($directory . '/' . $file);
                                }
                            } else {
                                if (!in_array($file, $data)) {
                                $this->fileSystem->remove($directory . '/' . $file);
                            }
                            }
                            
                        }
                    }
                }
            }
    }
}