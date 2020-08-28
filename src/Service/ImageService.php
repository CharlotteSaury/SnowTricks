<?php

namespace App\Service;

use App\Entity\Trick;
use App\Entity\User;
use App\Helper\ImageFileDeletor;
use App\Helper\UploaderHelper;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;

class ImageService
{
    /**
     * @var UploaderHelper
     */
    private $uploaderHelper;

    /**
     * @var ImageFileDeletor
     */
    private $imageFileDeletor;

    /**
     * @var Filesystem
     */
    private $fileSystem;

    /**
     * @var string
     */
    private $trickDirectory;

    public function __construct(UploaderHelper $uploaderHelper, ImageFileDeletor $imageFileDeletor, Filesystem $fileSystem, string $trickDirectory)
    {
        $this->uploaderHelper = $uploaderHelper;
        $this->imageFileDeletor = $imageFileDeletor;
        $this->fileSystem = $fileSystem;
        $this->trickDirectory = $trickDirectory;
    }

    /**
     * Handle new main image upload.
     *
     * @return void
     */
    public function handleMainImage(Trick $trick, Form $form)
    {
        try {
            $mainImage = $form->get('mainImage')->getData();
            if (!empty($mainImage)) {
                $mainImageName = $this->uploaderHelper->uploadFile($mainImage, 'tricks', 'trick_'.$trick->getId());
                $trick->setMainImage($mainImageName);
            }
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    /**
     * Handle new images upload.
     *
     * @return void
     */
    public function handleImages(Trick $trick, Form $form)
    {
        try {
            $images = $form->get('images')->getData();
            foreach ($images as $image) {
                if (null !== $image->getFile()) {
                    $imageName = $this->uploaderHelper->uploadFile($image->getFile(), 'tricks', 'trick_'.$trick->getId());

                    $image->setName($imageName);
                    $trick->addImage($image);
                }
            }
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    /**
     * Handle image file uploads deletion after trick edition if deleted.
     *
     * @return void
     */
    public function handleImageFiles(Trick $trick)
    {
        try {
            $trickImages = [$trick->getMainImage()];
            foreach ($trick->getImages() as $image) {
                array_push($trickImages, $image->getName());
            }
            $this->imageFileDeletor->deleteFile('trick', $trick->getId(), $trickImages);
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    /**
     * handle folder name edition after trick persistence.
     *
     * @return void
     */
    public function handleImageFolderRename(Trick $trick)
    {
        if ($this->fileSystem->exists($this->trickDirectory)) {
            $this->fileSystem->rename($this->trickDirectory, $this->trickDirectory.$trick->getId());
        }
    }

    /**
     * Handle image file uploads folder deletion after trick edition.
     *
     * @return void
     */
    public function handleImageFolderDeletion(Trick $trick)
    {
        $directory = $this->trickDirectory.$trick->getId();
        if ($this->fileSystem->exists($directory)) {
            $this->fileSystem->remove($directory);
        }
    }

    /**
     * Handle main image deletion.
     *
     * @return void
     */
    public function handleMainImageDeletion(Trick $trick)
    {
        try {
            $this->imageFileDeletor->deleteFile('trick', $trick->getId(), [$trick->getMainImage()], true);
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    /**
     * Handle reported main image edition.
     *
     * @return void
     */
    public function handleMainImageReport(Trick $reportedTrick, Request $request)
    {
        try {
            $trick = $reportedTrick->getParentTrick();
            if ($request->request->get('reported_mainImage')) {
                $trick->setMainImage($reportedTrick->getMainImage());
                $this->fileSystem->copy($this->trickDirectory.$reportedTrick->getId().'/'.$reportedTrick->getMainImage(), $this->trickDirectory.$reportedTrick->getParentTrick()->getId().'/'.$reportedTrick->getMainImage(), true);
            }
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    /**
     * Handle new reported images and deleted reported images.
     *
     * @return void
     */
    public function handleImageReport(Trick $reportedTrick, Request $request)
    {
        try {
            $trick = $reportedTrick->getParentTrick();
            foreach ($trick->getImages() as $image) {
                if ($request->request->get('image_'.$image->getId())) {
                    $trick->removeImage($image);
                }
            }
            foreach ($reportedTrick->getImages() as $image) {
                if ($request->request->get('reported_image_'.$image->getId())) {
                    $trick->addImage($image);
                    $this->fileSystem->copy($this->trickDirectory.$reportedTrick->getId().'/'.$image->getName(), $this->trickDirectory.$reportedTrick->getParentTrick()->getId().'/'.$image->getName(), true);
                }
            }
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    /**
     * Handle user avatar edition.
     *
     * @return void
     */
    public function handleAvatarEdition(User $user, Form $form)
    {
        try {
            $avatar = $form->get('avatar')->getData();
            if (!empty($avatar)) {
                $avatarName = $this->uploaderHelper->uploadFile($avatar, 'users', 'user_'.$user->getId());
                $user->setAvatar($avatarName);
            }
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    /**
     * Handle avatar file deletion after edition.
     *
     * @return void
     */
    public function handleAvatarFileDeletion(User $user, Form $form)
    {
        $avatar = $form->get('avatar')->getData();
        if (!empty($avatar)) {
            $this->imageFileDeletor->deleteFile('user', $user->getId(), [$user->getAvatar()]);
        }
    }
}
