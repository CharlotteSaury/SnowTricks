<?php

namespace App\Tests\Entity;

use DateTime;
use App\Entity\Trick;
use App\Tests\Utils\AssertHasErrors;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class TrickTest extends KernelTestCase
{
    use AssertHasErrors;
    use FixturesTrait;

    public function getEntity(): Trick
    {
        $fixtures = $this->loadFixtureFiles([
            dirname(__DIR__) . '/fixtures/users.yaml',
            dirname(__DIR__) . '/fixtures/tricks.yaml'
        ]);
        $trick = new Trick();
        $trick->setName('trick')
            ->setDescription('description')
            ->setCreatedAt(new \DateTime())
            ->setUpdatedAt(new \DateTime())
            ->setAuthor($fixtures['user1']);

        return $trick;
    }

    public function testValidEntity()
    {
        $this->assertHasErrors($this->getEntity(), 0);
    }

    public function testInvalidEntity()
    {
        $trick = $this->getEntity();
        $trick->setName('a')
            ->setDescription('short');
        $this->assertHasErrors($trick, 2);
    }

    public function testInvalidBlankName()
    {
        $this->assertHasErrors($this->getEntity()->setName(''), 1);
    }

    public function testInvalidUniqueName()
    {
        $trick = $this->getEntity()->setName('trick1');
        $this->assertHasErrors($trick, 1);
    }
}