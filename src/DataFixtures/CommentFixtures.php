<?php

namespace App\DataFixtures;

use App\Entity\Comment;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class CommentFixtures extends Fixture implements DependentFixtureInterface
{
    /**
     * Load comment fixtures.
     *
     * @return void
     */
    public function load(ObjectManager $manager)
    {
        $faker = \Faker\Factory::create();

        for ($i = 0; $i < 100; ++$i) {
            $comment = new Comment();

            $comment->setContent($faker->text(mt_rand(5, 300)))
                ->setAuthor($this->getReference('user'.mt_rand(0, 29)))
                ->setTrick($this->getReference('trick'.mt_rand(0, 29)))
                ->setCreatedAt($faker->dateTime('-30 days', '-15 days', null));
            $manager->persist($comment);
        }
        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            UserFixtures::class,
            TrickFixtures::class,
        ];
    }
}
