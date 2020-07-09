<?php

namespace App\DataFixtures;

use App\Entity\Role;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserRoleFixtures extends Fixture
{
    private $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }
    public function load(ObjectManager $manager)
    {
        $faker = \Faker\Factory::create();
        $roles = ['user', 'moderator', 'admin'];
        $j = 0;

        foreach ($roles as $key => $value) {
            $role = new Role();
            $role->setName($value);
            $manager->persist($role);

            for ($i = $j ; $i < ($j + 10); $i++) {
                $user = new User();
                $password = $this->encoder->encodePassword($user, $faker->word);
                $user->setEmail($faker->email)
                    ->setPassword($password)
                    ->setCreatedAt($faker->dateTime('now', null))
                    ->setAvatar($faker->imageUrl)
                    ->setFirstName($faker->firstName(null))
                    ->setLastName($faker->lastName)
                    ->setRole($role);
                $manager->persist($user);

                $this->addReference('user'. $i, $user);
            }
            $j += 10;
        }
        $manager->flush();
    }
}
