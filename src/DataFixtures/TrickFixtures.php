<?php

namespace App\DataFixtures;

use App\Entity\Image;
use App\Entity\Trick;
use App\Entity\Video;
use App\Helper\UploaderHelper;
use App\Helper\VideoLinkFormatter;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;

class TrickFixtures extends Fixture implements DependentFixtureInterface
{
    private $uploaderHelper;

    private $faker;

    private $videoLinkFormatter;

    public function __construct(VideoLinkFormatter $videoLinkFormatter, UploaderHelper $uploaderHelper)
    {
        $this->videoLinkFormatter = $videoLinkFormatter;
        $this->uploaderHelper = $uploaderHelper;
        $this->faker = \Faker\Factory::create();
    }

    public function load(ObjectManager $manager)
    {
        $videos = [
            'http://www.youtube.com/watch?v=AzJPhQdTRQQ&t=1s',
            'https://www.youtube.com/watch?v=axNnKy-jfWw',
            'www.youtube.com/watch?v=axNnKy-jfWw',
            'https://youtu.be/R2Cp1RumorU',
            'https://youtu.be/UGdif-dwu-8',
            'https://www.youtube.com/embed/M_BOfGX0aGs',
            'https://www.dailymotion.com/video/x4b4ga',
            'wwww.dailymotion.com/video/x2j4bgs',
            'dailymotion.com/video/xwpx9p',
            'https://dai.ly/xog7m7',
            'dai.ly/x72qhs9',
            'https://www.dailymotion.com/embed/video/x7vau0d',
            'dailymotion.com/embed/video/x6xb7gd',
            'https://vimeo.com/56415173',
            'vimeo.com/151351853',
            'https://vimeo.com/56688915',
            'vimeo.com/159485768',
            'http://vimeo.com/6097400',
            'https://player.vimeo.com/video/17859252',
        ];

        for ($i = 0; $i < 30; ++$i) {
            $trick = new Trick();
            $groups = [];
            for ($j = 1; $j < mt_rand(1, 4); ++$j) {
                array_push($groups, $this->getReference('group'.mt_rand(0, 5)));
            }

            $trick->setName($this->faker->word)
                ->setDescription($this->faker->text(mt_rand(200, 3000)))
                ->setAuthor($this->getReference('user'.mt_rand(0, 29)))
                ->setCreatedAt($this->faker->dateTimeBetween('-30 days', '-15 days', null))
                ->setUpdatedAt($this->faker->dateTimeBetween('-15 days', 'now', null))
                ->setMainImage($this->fakeUploadImage($i));

            for ($k = 0; $k < mt_rand(0, 4); ++$k) {
                $image = new Image();
                $image->setName($this->fakeUploadImage($i));
                $trick->addImage($image);
            }

            foreach ($groups as $key => $value) {
                $trick->addGroup($value);
            }

            for ($k = 0; $k < mt_rand(1, 4); ++$k) {
                $video = new Video();
                $formattedName = $this->videoLinkFormatter->format($videos[mt_rand(0, \count($videos) - 1)]);
                $video->setName($formattedName);
                $trick->addVideo($video);
            }

            $manager->persist($trick);

            $this->addReference('trick'.$i, $trick);
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            UserFixtures::class,
            GroupFixtures::class,
        ];
    }

    private function fakeUploadImage($trickId): string
    {
        $trickImages = ['image1.jpg', 'image2.jpg', 'image3.jpg', 'image4.jpg', 'image5.jpg', 'image6.jpg', 'image7.jpg', 'image8.jpg', 'image9.jpg'];
        $randomImage = $this->faker->randomElement($trickImages);
        $fileSystem = new Filesystem();
        $targetPath = sys_get_temp_dir().'/'.$randomImage;
        $fileSystem->copy(__DIR__.'/images/trick/'.$randomImage, $targetPath, true);

        return $this->uploaderHelper
            ->uploadFile(new File($targetPath), 'tricks', 'trick_'.($trickId + 1).'/');
    }
}
