<?php

namespace App\Tests;

use App\Tests\Utils\NeedLogin;
use Symfony\Component\HttpFoundation\Response;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AdminControllerTest extends WebTestCase
{
    use NeedLogin;
    use FixturesTrait;

    public function testStatisticPageNotAuthenticated()
    {
        $client = static::createClient();
        $client->request('GET', '/admin/statistics');
        $this->assertResponseRedirects();
    }
    
    public function testStatisticPageAuthenticatedUser()
    {
        $client = static::createClient();
        $users = $this->loadFixtureFiles([dirname(__DIR__) . '/fixtures/users.yaml']);
        $this->login($client, $users['user_user']);
        $client->request('GET', '/admin/statistics');
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testStatisticPageAuthenticatedModerator()
    {
        $client = static::createClient();
        $users = $this->loadFixtureFiles([dirname(__DIR__) . '/fixtures/users.yaml']);
        $this->login($client, $users['user_moderator']);
        $client->request('GET', '/admin/statistics');
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testStatisticPageAuthenticatedAdmin()
    {
        $client = static::createClient();
        $users = $this->loadFixtureFiles([dirname(__DIR__) . '/fixtures/users.yaml']);
        $this->login($client, $users['user_admin']);
        $client->request('GET', '/admin/statistics');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorExists('h6', 'Statistics');
        $this->assertSelectorExists('#adminUserCard');
        $this->assertSelectorExists('#adminTrickCard');
        $this->assertSelectorExists('#adminCommentCard');
    }

}