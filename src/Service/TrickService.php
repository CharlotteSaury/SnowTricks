<?php

namespace App\Service;

use DateTime;
use Exception;
use App\Entity\User;
use App\Entity\Trick;
use Symfony\Component\Form\Form;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class TrickService
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var VideoService
     */
    private $videoService;

    /**
     * @var ImageService
     */
    private $imageService;

    public function __construct(EntityManagerInterface $entityManager, VideoService $videoService, ImageService $imageService)
    {
        $this->entityManager = $entityManager;
        $this->videoService = $videoService;
        $this->imageService = $imageService;
    }

    public function handleCreateOrUpdate(Trick $trick, Form $form, User $author)
    {
        try {
            $trick->setAuthor($author);
            $this->imageService->handleMainImage($trick, $form);
            $this->imageService->handleImages($trick, $form);
            $this->videoService->handleNewVideos($trick, $form);

            if ($trick->getId() != null) {
                $trick->setUpdatedAt(new \DateTime());
            }

            $this->entityManager->persist($trick);
            $this->entityManager->flush();
            $this->imageService->handleImageFolderRename($trick);
            $this->imageService->handleImageFiles($trick);

            return $trick;
        } catch (\Exception $exception) {
            throw $exception;
        }
        
    }

    public function handleTrickDeletion(Trick $trick)
    {
        try {
            $this->imageService->handleImageFolderDeletion($trick);
            $this->entityManager->remove($trick);
            $this->entityManager->flush();
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    public function handleMainImageDeletion(Trick $trick)
    {
        try {
            $this->imageService->handleMainImageDeletion($trick);
            $trick->setMainImage(null);
            $this->entityManager->persist($trick);
            $this->entityManager->flush();
        } catch (\Exception $exception) {
            throw $exception;
        }
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
            $this->imageService->handleMainImageReport($reportedTrick, $request);
            $this->imageService->handleImageReport($reportedTrick, $request);
            $this->videoService->handleVideoReport($reportedTrick, $request);
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

            $this->imageService->handleImageFolderDeletion($reportedTrick);
            $this->imageService->handleImageFiles($trick);
            
        } catch (\Exception $error) {
            throw $error;
        }
    }

    
}
