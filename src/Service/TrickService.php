<?php

namespace App\Service;

use DateTime;
use Exception;
use App\Entity\User;
use App\Entity\Trick;
use App\Helper\UploaderHelper;
use App\Helper\ImageFileDeletor;
use Symfony\Component\Form\Form;
use App\Helper\VideoLinkFormatter;
use Psr\Container\ContainerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class TrickService
{
    private $entityManager;

    private $uploaderHelper;

    private $videoLinkFormatter;

    private $fileSystem;

    private $trickDirectory;

    private $imageFileDeletor;

    private $container;

    private $session;

    const NEW_FLASH = 'Your trick is posted !';

    const EDIT_FLASH_SELF = 'Your trick has been updated !';

    const EDIT_FLASH = '\'s trick has been updated !';

    public function __construct(EntityManagerInterface $entityManager, UploaderHelper $uploaderHelper, VideoLinkFormatter $videoLinkFormatter, Filesystem $fileSystem, string $trickDirectory, ImageFileDeletor $imageFileDeletor, ContainerInterface $container, SessionInterface $session)
    {
        $this->entityManager = $entityManager;
        $this->uploaderHelper = $uploaderHelper;
        $this->videoLinkFormatter = $videoLinkFormatter;
        $this->fileSystem = $fileSystem;
        $this->trickDirectory = $trickDirectory;
        $this->imageFileDeletor = $imageFileDeletor;
        $this->container = $container;
        $this->session = $session;
    }

    public function handleCreateOrUpdate(Trick $trick, Form $form, User $author)
    {
        $trick->setAuthor($author);
        $this->handleMainImage($trick, $form);
        $this->handleImages($trick, $form);
        $this->handleVideos($trick, $form);

        if ($trick->getId() != null) {
            $trick->setUpdatedAt(new \DateTime());
        }

        $message = $this->createFlashMessage($trick);
        $this->addFlashMessage($message, 'success');

        $this->entityManager->persist($trick);
        $this->entityManager->flush();

        $this->handleImageFiles($trick);

        return $trick;
    }

    /**
     * Add success Flash message to session
     *
     * @param Trick $trick
     * @return void
     */
    public function addFlashMessage(string $message, string $type)
    {
        if (!$this->container->has('session')) {
            throw new \LogicException('You can not use the addFlash method if sessions are disabled. Enable them in "config/packages/framework.yaml".');
        }
        $this->container->get('session')->getFlashBag()->add($type, $message);
    }

    /**
     * Create Flash message content
     *
     * @param Trick $trick
     * @return string
     */
    public function createFlashMessage(Trick $trick)
    {
        if ($trick->getId() != null) {
            if ($trick->getAuthor() == $this->session->get('user')) {
                return self::EDIT_FLASH_SELF;
            }
            return $trick->getAuthor()->getUsername() . self::EDIT_FLASH;
        }
        if ($trick->getParentTrick() != null) {
            return 'A notification has been sent to ' . $trick->getAuthor()->getUsername() . 'for modification request';
        }
        return self::NEW_FLASH;
    }

    public function handleMainImage(Trick $trick, Form $form)
    {
        $mainImage = $form->get('mainImage')->getData();
        if (!empty($mainImage)) {
            $mainImageName = $this->uploaderHelper->uploadFile($mainImage, 'tricks', 'trick_' . $trick->getId());
            $trick->setMainImage($mainImageName);
        }
    }

    public function handleImages(Trick $trick, Form $form)
    {
        $images = $form->get('images')->getData();
        foreach ($images as $image) {
            if ($image->getFile() != null) {
                $imageName = $this->uploaderHelper->uploadFile($image->getFile(), 'tricks', 'trick_' . $trick->getId());

                $image->setName($imageName);
                $trick->addImage($image);
            }
        }
    }

    public function handleVideos(Trick $trick, Form $form)
    {
        $videos = $form->get('videos')->getData();
        foreach ($videos as $video) {
            if ($video->getLink() != null) {
                $formattedName = $this->videoLinkFormatter->format($video->getLink());
                $video->setName($formattedName);
                $trick->addVideo($video);
            }
        }
    }

    public function handleImageFiles(Trick $trick)
    {
        if ($this->fileSystem->exists($this->trickDirectory)) {
            $this->fileSystem->rename($this->trickDirectory, $this->trickDirectory . $trick->getId());
        }

        $trickImages = [$trick->getMainImage()];
        foreach ($trick->getImages() as $image) {
            array_push($trickImages, $image->getName());
        }
        $this->imageFileDeletor->deleteFile('trick', $trick->getId(), $trickImages);
    }

    public function handleReport(Trick $reportedTrick, Request $request)
    {
        try {
            $trick = $reportedTrick->getParentTrick();
            if ($request->request->get('reported_name')) {
                $trick->setName($reportedTrick->getName());
            }
            if ($request->request->get('reported_description')) {
                $trick->setDescription($reportedTrick->getDescription());
            }
            if ($request->request->get('reported_mainImage')) {
                $trick->setMainImage($reportedTrick->getMainImage());
                $fileSystem = new Filesystem();
                $fileSystem->copy($this->trickDirectory . $reportedTrick->getId() . '/' . $reportedTrick->getMainImage(), $this->trickDirectory . $reportedTrick->getParentTrick()->getId() . '/' . $reportedTrick->getMainImage(), true);
            }
            foreach ($trick->getImages() as $image) {
                if ($request->request->get('image_' . $image->getId())) {
                    $trick->removeImage($image);
                }
            }
            foreach ($reportedTrick->getImages() as $image) {
                if ($request->request->get('reported_image_' . $image->getId())) {
                    $trick->addImage($image);
                    $fileSystem = new Filesystem();
                    $fileSystem->copy($this->trickDirectory . $reportedTrick->getId() . '/' . $image->getName(), $this->trickDirectory . $reportedTrick->getParentTrick()->getId() . '/' . $image->getName(), true);
                }
            }
            foreach ($trick->getVideos() as $video) {
                if ($request->request->get('video_' . $video->getId())) {
                    $trick->removeVideo($video);
                }
            }
            foreach ($reportedTrick->getVideos() as $video) {
                if ($request->request->get('reported_video_' . $video->getId())) {
                    $trick->addVideo($video);
                }
            }
            foreach ($trick->getGroups() as $group) {
                if ($request->request->get('group_' . $group->getId())) {
                    $trick->removeGroup($group);
                }
            }
            foreach ($reportedTrick->getGroups() as $group) {
                if ($request->request->get('reported_group_' . $group->getId())) {
                    $trick->addGroup($group);
                }
            }

            $trick->setUpdatedAt(new DateTime());

            $this->entityManager->persist($trick);
            $this->entityManager->remove($reportedTrick);
            $this->entityManager->flush();

            if ($directory = $this->trickDirectory . $reportedTrick->getId()) {
                $this->fileSystem->remove($directory);
            }

            $trickImages = [$trick->getMainImage()];
            foreach ($trick->getImages() as $image) {
                array_push($trickImages, $image->getName());
            }
            $this->imageFileDeletor->deleteFile('trick', $trick->getId(), $trickImages);
        } catch (\Exception $error) {
            throw $error;
        }
    }
}
