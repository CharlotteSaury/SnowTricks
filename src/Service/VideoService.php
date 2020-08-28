<?php

namespace App\Service;

use App\Entity\Trick;
use App\Helper\VideoLinkFormatter;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;

class VideoService
{
    private $videoLinkFormatter;

    public function __construct(VideoLinkFormatter $videoLinkFormatter)
    {
        $this->videoLinkFormatter = $videoLinkFormatter;
    }

    public function handleNewVideos(Trick $trick, Form $form)
    {
        try {
            $videos = $form->get('videos')->getData();
            foreach ($videos as $video) {
                if (null !== $video->getLink()) {
                    $formattedName = $this->videoLinkFormatter->format($video->getLink());
                    $video->setName($formattedName);
                    $trick->addVideo($video);
                }
            }
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    public function handleVideoReport(Trick $reportedTrick, Request $request)
    {
        try {
            $trick = $reportedTrick->getParentTrick();
            foreach ($trick->getVideos() as $video) {
                if ($request->request->get('video_'.$video->getId())) {
                    $trick->removeVideo($video);
                }
            }
            foreach ($reportedTrick->getVideos() as $video) {
                if ($request->request->get('reported_video_'.$video->getId())) {
                    $trick->addVideo($video);
                }
            }
        } catch (\Exception $exception) {
            throw $exception;
        }
    }
}
