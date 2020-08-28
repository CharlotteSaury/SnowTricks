<?php

namespace App\Helper;

use Symfony\Component\Filesystem\Filesystem;

class ImageFileDeletor
{
    /**
     * @var string
     */
    private $trickDirectory;

    /**
     * @var string
     */
    private $userDirectory;

    /**
     * @var Filesystem
     */
    private $fileSystem;

    public function __construct(string $trickDirectory, string $userDirectory, Filesystem $fileSystem)
    {
        $this->trickDirectory = $trickDirectory;
        $this->userDirectory = $userDirectory;
        $this->fileSystem = $fileSystem;
    }

    /**
     * Delete image files that are not associated with user or trick after edition/deletion.
     *
     * @param bool $bool
     *
     * @return void
     */
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
