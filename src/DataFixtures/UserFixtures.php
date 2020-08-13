<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixtures extends Fixture
{
    private $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }
    public function load(ObjectManager $manager)
    {
        $faker = \Faker\Factory::create();
        $roles = [["ROLE_ADMIN"], ["ROLE_USER"], ["ROLE_MODERATOR"]];

        for ($i = 0; $i < 30; $i++) {
            $user = new User();
            $password = $this->encoder->encodePassword($user, $faker->word);
            $user->setUsername($faker->word)
                ->setEmail($faker->email)
                ->setPassword($password)
                ->setCreatedAt($faker->dateTime('now', null))
                ->setAvatar($faker->imageUrl(200, 200, 'people'))
                ->setFirstName($faker->firstName(null))
                ->setLastName($faker->lastName)
                ->setDescription($faker->text(mt_rand(50, 2000)));
            $manager->persist($user);

            $this->addReference('user' . $i, $user);
        }

        $fakeUsers = [
            'User' => [
                'User1*',
                ["ROLE_USER"]
            ],
            'Moderator' => [
                'Moderator1*',
                ["ROLE_MODERATOR"]
            ],
            'Admin' => [
                'Admin1*',
                ["ROLE_ADMIN"]
            ]
            ];

        foreach ($fakeUsers as $fakeUser) {
            $user = new User();
            $password = $this->encoder->encodePassword($user, $fakeUser[0]);
            $user->setUsername(array_search($fakeUser, $fakeUsers))
                ->setEmail($faker->email)
                ->setPassword($password)
                ->setRoles($fakeUser[1]);
            $manager->persist($user);
        }
        $manager->flush();
    }
}
