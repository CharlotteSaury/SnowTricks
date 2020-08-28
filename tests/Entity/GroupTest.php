<?php

namespace App\Tests\Entity;

use App\Entity\Group;
use App\Tests\Utils\AssertHasErrors;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class GroupTest extends KernelTestCase
{
    use AssertHasErrors;
    use FixturesTrait;

    public function testValidEntity()
    {
        $group = new Group();
        $group->setName('Rotation');
        $this->assertHasErrors($group, 0);
    }

    public function testInvalidEntity()
    {
        $group = new Group();
        $group->setName('A');
        $this->assertHasErrors($group, 1);
    }

    public function testInvalidBlankName()
    {
        $group = new Group();
        $group->setName('');
        $this->assertHasErrors($group, 2);
    }

    public function testInvalidUniqueName()
    {
        $this->loadFixtureFiles([
            \dirname(__DIR__).'/fixtures/groups.yaml',
        ]);
        $group = new Group();
        $group->setName('grab');
        $this->assertHasErrors($group, 1);
    }
}
