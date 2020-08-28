<?php

namespace App\Helper;

class VideoLinkFormatter
{
    const REGEX_YOUTUBE = '#(?:(?:youtube\.com|youtu\.be))(?:\/(?:[\w\-]+\?v=|embed\/)?)([\w\-]+)(?:\S+)?$#';
    const REGEX_DAILYMOTION = '#(?:(?:dai\.ly|dailymotion\.com))(?:\/(?:video\/|embed\/video\/)?)([\w\-]+)(?:\S+)?$#';
    const REGEX_VIMEO = '#(?:(?:vimeo\.com|player\.vimeo\.com))(?:\/(?:video\/)?)([\w\-]+)(?:\S+)?$#';
    const URL_YOUTUBE = 'https://www.youtube.com/embed/';
    const URL_DAILYMOTION = 'https://www.dailymotion.com/embed/video/';
    const URL_VIMEO = 'https://player.vimeo.com/video/';

    public function format(string $link): string
    {
        if (preg_match(self::REGEX_YOUTUBE, $link, $matches)) {
            $formattedName = self::URL_YOUTUBE.$matches[1];
        } elseif (preg_match(self::REGEX_DAILYMOTION, $link, $matches)) {
            $formattedName = self::URL_DAILYMOTION.$matches[1];
        } elseif (preg_match(self::REGEX_VIMEO, $link, $matches)) {
            $formattedName = self::URL_VIMEO.$matches[1];
        }

        return $formattedName;
    }
}
