<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\Trick;
use App\Entity\ReportedTrick;

class ReportedTrickGenerator
{
    public function transform(Trick $trick, User $user)
    {
        $reportedTrick = new ReportedTrick();
        $reportedTrick->setName($trick->getName())
            ->setDescription($trick->getDescription())
            ->setMainImage($trick->getMainImage())
            ->setTrick($trick)
            ->setUser($user);
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