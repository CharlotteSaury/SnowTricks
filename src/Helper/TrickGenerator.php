<?php

namespace App\Helper;

use App\Entity\Trick;
use App\Entity\User;
use App\Repository\TrickRepository;

class TrickGenerator
{
    /**
     * @var TrickRepository
     */
    private $trickRepository;

    public function __construct(TrickRepository $trickRepository)
    {
        $this->trickRepository = $trickRepository;
    }

    /**
     * Handle reported trick creation by clining parent trick properties.
     *
     * @return Trick 
     */
    public function clone(Trick $trick, User $user)
    {
        $reportedTricks = \count($this->trickRepository->findBy(['parentTrick' => $trick]));
        $reportedTrick = new Trick();
        $reportedTrick->setName($trick->getName().'('.($reportedTricks + 1).')')
            ->setDescription($trick->getDescription())
            ->setMainImage($trick->getMainImage())
            ->setParentTrick($trick)
            ->setAuthor($user);
        foreach ($trick->getGroups() as $group) {
            $reportedTrick->addGroup($group);
        }
        foreach ($trick->getImages() as $image) {
            $reportedTrick->addImage($image);
        }
        foreach ($trick->getVideos() as $video) {
            $reportedTrick->addVideo($video);
        }

        return $reportedTrick;
    }
}
