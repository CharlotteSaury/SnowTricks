<?php

namespace App\Tests\Entity;

use DateTime;
use Faker\Factory;
use App\Entity\Comment;
use App\Tests\Utils\AssertHasErrors;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CommentTest extends KernelTestCase
{
    use AssertHasErrors;
    use FixturesTrait;

    public function getEntity(): Comment
    {
        $fixtures = $this->loadFixtureFiles([
            dirname(__DIR__) . '/fixtures/users.yaml',
            dirname(__DIR__) . '/fixtures/tricks.yaml'
        ]);
        $comment = new Comment();
        $comment->setContent('comment 1')
            ->setCreatedAt(new \DateTime())
            ->setAuthor($fixtures['user1'])
            ->setTrick($fixtures['trick1']);

        return $comment;
    }

    public function testValidEntity()
    {
        $this->assertHasErrors($this->getEntity(), 0);
    }

    public function testInvalidContent()
    {
        $this->assertHasErrors($this->getEntity()->setContent('a'), 1);
    }

    public function testInvalidBlankContent()
    {
        $this->assertHasErrors($this->getEntity()->setContent(''), 2);
    }
    
}