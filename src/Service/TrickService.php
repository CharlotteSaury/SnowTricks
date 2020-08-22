<?php

namespace App\Service;

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
        try {
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
        } catch (\Exception $error) {
            throw $error->getMessage();
        }
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
        } else {
            if ($trick->getParentTrick() != null) {
                return 'A notification has been sent to ' . $trick->getAuthor()->getUsername() . 'for modification request';
            } 
            return self::NEW_FLASH;
        }
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
}
