<?php

namespace App\Helper;

use Symfony\Component\Filesystem\Filesystem;

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

    public function deleteFile(string $type, int $id, array $data, bool $bool = null)
    {
        if ('trick' === $type) {
            $directory = $this->trickDirectory.$id;
        } elseif ('user' === $type) {
            $directory = $this->userDirectory.$id;
        }
        if ($this->fileSystem->exists($directory)) {
            if (opendir($directory)) {
                foreach (scandir($directory) as $file) {
                    if ('.' !== $file && '..' !== $file) {
                        if ($bool) {
                            if (\in_array($file, $data, true)) {
                                $this->fileSystem->remove($directory.'/'.$file);
                            }
                        } else {
                            if (!\in_array($file, $data, true)) {
                                $this->fileSystem->remove($directory.'/'.$file);
                            }
                        }
                    }
                }
            }
        }
    }
}
