<?php

namespace App\Helper;

use App\Entity\User;
use App\Entity\Trick;
use App\Repository\TrickRepository;

class ReportedTrickGenerator
{
    private $trickRepoditory;

    public function __construct(TrickRepository $trickRepository)
    {
        $this->trickRepository = $trickRepository;
    }
    public function transform(Trick $trick, User $user)
    {
        $reportedTricks = count($this->trickRepository->findBy(['parentTrick' => $trick]));
        $reportedTrick = new Trick();
        $reportedTrick->setName($trick->getName() . '(' . ($reportedTricks + 1) . ')')
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
