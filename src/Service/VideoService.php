<?php

namespace App\Service;

use App\Entity\Trick;
use App\Helper\VideoLinkFormatter;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;

class VideoService
{
    /**
     * @var VideoLinkFormatter
     */
    private $videoLinkFormatter;

    public function __construct(VideoLinkFormatter $videoLinkFormatter)
    {
        $this->videoLinkFormatter = $videoLinkFormatter;
    }

    /**
     * Handle formatting new video link and creation in database.
     *
     * @return void
     */
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

    /**
     * Handle video creation/deletion after trick report.
     *
     * @return void
     */
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
