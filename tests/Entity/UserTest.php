<?php

namespace App\Tests\Entity;

use DateTime;
use App\Entity\User;
use App\Tests\Utils\AssertHasErrors;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UserTest extends KernelTestCase
{
    use AssertHasErrors;
    use FixturesTrait;

    public function getEntity(): User
    {
        $user = new User();
        $plainPassword = 'aaaaaaA1*';
        $user->setUsername('Usertest')
            ->setEmail('test@email.com')
            ->setPlainPassword($plainPassword)
            ->setPassword('password')
            ->setCreatedAt(new \DateTime())
            ->setRoles(['ROLE_USER']);

        return $user;
    }

    public function testValidEntity()
    {
        $this->assertHasErrors($this->getEntity(), 0);
    }

    public function testInvalidUsername()
    {
        $this->assertHasErrors($this->getEntity()->setUsername('aze'), 1);
        $this->assertHasErrors($this->getEntity()->setUsername('azertyuiopqsdfghjklmwxcvbnazerttyui'), 1);
    }

    public function testInvalidBlankUsername()
    {
        $this->assertHasErrors($this->getEntity()->setUsername(''), 2);
    }

    public function testInvalidEmail()
    {
        $invalidEmails = [
            'azerty',
            'azerty@',
            '@email.com',
            '@email',
            'azerty@email'
        ];
        foreach ($invalidEmails as $invalidEmail) {
            $this->assertHasErrors($this->getEntity()->setEmail($invalidEmail), 1);
        }
    }

    public function testInvalidBlankEmail()
    {
        $this->assertHasErrors($this->getEntity()->setEmail(''), 1);
    }

    public function testInvalidFirstOrLastName()
    {
        $invalidNames = [
            123456,
            'User1',
            'Namelonguerthanthirtycharacters'
        ];
        foreach ($invalidNames as $invalidName) {
            $this->assertHasErrors($this->getEntity()->setFirstName($invalidName), 1);
            $this->assertHasErrors($this->getEntity()->setLastName($invalidName), 1);
        }
    }

    public function testInvalidUniqueUsername()
    {
        $user = $this->getEntity()->setUsername('uniqueuser');
        $this->loadFixtureFiles([dirname(__DIR__) . '/fixtures/users.yaml']);
        $this->assertHasErrors($user, 1);
    }

    public function testInvalidUniqueEmail()
    {
        $user = $this->getEntity()->setEmail('unique@email.com');
        $this->loadFixtureFiles([dirname(__DIR__) . '/fixtures/users.yaml']);
        $this->assertHasErrors($user, 1);
    }
}