<?php

namespace App\DataFixtures;

use App\Entity\Trick;
use App\Entity\Video;
use App\DataFixtures\GroupFixtures;
use App\Service\VideoLinkFormatter;
use App\DataFixtures\UserRoleFixtures;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class TrickFixtures extends Fixture implements DependentFixtureInterface
{
    private $videoLinkFormatter;

    public function __construct(VideoLinkFormatter $videoLinkFormatter)
    {
        $this->videoLinkFormatter = $videoLinkFormatter;
    }
    public function load(ObjectManager $manager)
    {
        $faker = \Faker\Factory::create();
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
            'https://player.vimeo.com/video/17859252'
        ];

        for ($i = 0; $i < 30; $i++) {
            $trick = new Trick();
            $groups = [];
            for ($j = 1; $j < mt_rand(1,4); $j++) {
                array_push($groups, $this->getReference('group' . mt_rand(0, 5)));
            }

            $trick->setName($faker->word)
                ->setDescription($faker->text(mt_rand(200, 3000)))
                ->setAuthor($this->getReference('user' . mt_rand(0, 29)))
                ->setCreatedAt($faker->dateTimeBetween('-30 days', '-15 days', null))
                ->setUpdatedAt($faker->dateTimeBetween('-15 days', 'now', null));
            
            foreach ($groups as $key => $value) {
                $trick->addGroup($value);
            }

            for ($k = 0; $k < mt_rand(1, 4); $k++) {
                $video = new Video();
                $formattedName = $this->videoLinkFormatter->format($videos[mt_rand(0, count($videos)-1)]);
                $video->setName($formattedName);
                $trick->addVideo($video);
            }
                
            $manager->persist($trick);

            $this->addReference('trick' . $i, $trick);
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return array(
            UserFixtures::class,
            GroupFixtures::class
        );
    }
}
