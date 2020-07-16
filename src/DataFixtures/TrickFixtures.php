<?php

namespace App\DataFixtures;

use App\Entity\Trick;
use App\DataFixtures\UserRoleFixtures;
use App\DataFixtures\GroupFixtures;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class TrickFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $faker = \Faker\Factory::create();


        for ($i = 0; $i < 30; $i++) {
            $trick = new Trick();
            $groups = [];
            for ($j = 1; $j < mt_rand(0,4); $j++) {
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
