<?php

namespace App\Tests\Entity;

use DateTime;
use App\Entity\User;
use App\Tests\Utils\AssertHasErrors;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UserTest extends KernelTestCase
{
    use AssertHasErrors;

    public function getEntity(): User
    {
        $user = new User();
        $plainPassword = 'aaaaaaA1*';
        $user->setUsername('Usertest')
            ->setEmail('test@email.com')
            ->setPlainPassword($plainPassword)
            ->setPassword('$argon2id$v=19$m=65536,t=4,p=1$aWpLQmtzUUxuVjNhZ0pBOA$fivXTMz9mOXmqDHSk8z45nO2EbAk0yiFfT9ifAw3jlA')
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


    /*public function testInvalidRegexPlainPassword()
    {
        $invalidPlainPasswords = [
            'azerty',
            'Azerty',
            'Azerty1'
        ];
        foreach ($invalidPlainPasswords as $invalidPlainPassword) {
            $this->assertHasErrors($this->getEntity()->setPlainPassword($invalidPlainPassword), 1);
        }
    }
    public function testInvalidLengthPlainPassword()
    {
        $invalidPlainPasswords = [
            'aze',
            'thispasswordislonguerthan30characters'
        ];
        foreach ($invalidPlainPasswords as $invalidPlainPassword) {
            $this->assertHasErrors($this->getEntity()->setPlainPassword($invalidPlainPassword), 1);
        }
    }

    public function testInvalidBlankPlainPassword()
    {
        $this->assertHasErrors($this->getEntity()->setPlainPassword(null), 1);
        //$this->assertHasErrors($this->getEntity()->setPlainPassword(''), 1);
    }*/

    /*public function testInvalidUniqueUsername()
    {
        
    }

    public function testInvalidUniqueEmail()
    {
        
    }*/
}