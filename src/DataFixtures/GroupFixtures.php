<?php

namespace App\DataFixtures;

use App\Entity\Group;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class GroupFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $faker = \Faker\Factory::create();
        $groups = ['grab', 'rotation', 'flip', 'slide', 'one foot trick', 'old school'];

        for ($i = 0; $i < count($groups); $i++) {
            $group = new Group();
            $group->setName($groups[$i]);
            $manager->persist($group);
            $this->addReference('group'. $i, $group);
        }
        $manager->flush();
    }
}
