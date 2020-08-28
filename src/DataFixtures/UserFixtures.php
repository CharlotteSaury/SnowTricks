<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Helper\UploaderHelper;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixtures extends Fixture
{
    /**
     * @var UserPasswordEncoderInterface
     */
    private $encoder;

    private $faker;

    /**
     * @var UploaderHelper
     */
    private $uploaderHelper;

    public function __construct(UserPasswordEncoderInterface $encoder, UploaderHelper $uploaderHelper)
    {
        $this->encoder = $encoder;
        $this->faker = \Faker\Factory::create();
        $this->uploaderHelper = $uploaderHelper;
    }

    /**
     * Load user fixtures.
     *
     * @return void
     */
    public function load(ObjectManager $manager)
    {
        $roles = [['ROLE_ADMIN'], ['ROLE_USER'], ['ROLE_MODERATOR']];

        for ($i = 0; $i < 30; ++$i) {
            $user = new User();
            $password = $this->encoder->encodePassword($user, $this->faker->word);
            $user->setUsername($this->faker->word)
                ->setEmail($this->faker->email)
                ->setPassword($password)
                ->setCreatedAt($this->faker->dateTime('now', null))
                ->setFirstName($this->faker->firstName(null))
                ->setLastName($this->faker->lastName)
                ->setDescription($this->faker->text(mt_rand(50, 2000)))
                ->setAvatar($this->fakeUploadImage($i))
                ->setRoles($roles[mt_rand(0, 2)]);
            $manager->persist($user);

            $this->addReference('user'.$i, $user);
        }

        $fakeUsers = [
            'User' => [
                'User1*',
                ['ROLE_USER'],
            ],
            'Moderator' => [
                'Moderator1*',
                ['ROLE_MODERATOR'],
            ],
            'Admin' => [
                'Admin1*',
                ['ROLE_ADMIN'],
            ],
            ];

        foreach ($fakeUsers as $fakeUser) {
            $user = new User();
            $password = $this->encoder->encodePassword($user, $fakeUser[0]);
            $user->setUsername(array_search($fakeUser, $fakeUsers, true))
                ->setEmail($this->faker->email)
                ->setPassword($password)
                ->setRoles($fakeUser[1]);
            $manager->persist($user);
        }
        $manager->flush();
    }

    private function fakeUploadImage(int $userId): string
    {
        $userImages = ['user1.jpg', 'user2.jpg', 'user3.jpg', 'user4.jpg', 'user5.jpg', 'user6.jpg', 'user7.jpg'];
        $randomImage = $this->faker->randomElement($userImages);
        $fileSystem = new Filesystem();
        $targetPath = sys_get_temp_dir().'/'.$randomImage;
        $fileSystem->copy(__DIR__.'/images/user/'.$randomImage, $targetPath, true);

        return $this->uploaderHelper
            ->uploadFile(new File($targetPath), 'users', 'user_'.($userId + 1).'/');
    }
}
