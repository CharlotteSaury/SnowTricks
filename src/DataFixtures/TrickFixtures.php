<?php

namespace App\DataFixtures;

use App\Entity\Image;
use App\Entity\Trick;
use App\Service\UploaderHelper;
use App\DataFixtures\GroupFixtures;
use App\DataFixtures\UserRoleFixtures;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\Filesystem\Filesystem;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\File\File;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class TrickFixtures extends Fixture implements DependentFixtureInterface
{
    private $uploaderHelper;

    private $faker;

    public function __construct(UploaderHelper $uploaderHelper)
    {
        $this->uploaderHelper = $uploaderHelper;
        $this->faker = \Faker\Factory::create();
    }

    public function load(ObjectManager $manager)
    {
        for ($i = 0; $i < 30; $i++) {
            $trick = new Trick();
            $groups = [];
            for ($j = 1; $j < mt_rand(1, 4); $j++) {
                array_push($groups, $this->getReference('group' . mt_rand(0, 5)));
            }

            $trick->setName($this->faker->word)
                ->setDescription($this->faker->text(mt_rand(200, 3000)))
                ->setAuthor($this->getReference('user' . mt_rand(0, 29)))
                ->setCreatedAt($this->faker->dateTimeBetween('-30 days', '-15 days', null))
                ->setUpdatedAt($this->faker->dateTimeBetween('-15 days', 'now', null))
                ->setMainImage($this->fakeUploadImage($i));
            
            for ($k = 0; $k < mt_rand(0, 4); $k++) {
                $image = new Image();
                $image->setName($this->fakeUploadImage($i));
                $trick->addImage($image);
            }
                

            foreach ($groups as $key => $value) {
                $trick->addGroup($value);
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

    private function fakeUploadImage($trickId) :string
    {
        $trickImages = ['image1.jpg', 'image2.jpg', 'image3.jpg', 'image4.jpg', 'image5.jpg', 'image6.jpg', 'image7.jpg', 'image8.jpg', 'image9.jpg'];
        $randomImage = $this->faker->randomElement($trickImages);
        $fileSystem = new Filesystem();
        $targetPath = sys_get_temp_dir().'/'.$randomImage;
        $fileSystem->copy(__DIR__.'/images/trick/'.$randomImage, $targetPath, true);
        return $this->uploaderHelper
            ->uploadFile(new File($targetPath), 'tricks/trick_' . ($trickId + 1) . '/');
    }
}
