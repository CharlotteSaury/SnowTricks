<?php

namespace App\DataFixtures;


use App\Entity\Comment;
use App\DataFixtures\UserRoleFixtures;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class CommentFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $faker = \Faker\Factory::create();

        for ($i = 0; $i < 30; $i++) {
            $comment = new Comment();

            $comment->setContent($faker->text(mt_rand(5, 300)))
                ->setAuthor($this->getReference('user' . mt_rand(0, 29)))
                ->setTrick($this->getReference('trick' . mt_rand(0, 29)))
                ->setCreatedAt($faker->dateTime('-30 days', '-15 days', null));
            $manager->persist($comment);
        }
        $manager->flush();
    }

    public function getDependencies()
    {
        return array(
            UserFixtures::class,
            TrickFixtures::class
        );
    }
}
