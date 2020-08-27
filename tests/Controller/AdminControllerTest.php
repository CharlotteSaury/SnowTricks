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

    /**
     * Test redirection to login page when trying to access statistics for visitors
     */
    public function testStatisticPageNotAuthenticated()
    {
        $client = static::createClient();
        $client->request('GET', '/admin/statistics');
        $this->assertResponseRedirects();
    }
    
    /**
     * Test forbidden access to statistics page when not admin
     */
    public function testStatisticPageAuthenticatedUser()
    {
        $client = static::createClient();
        $users = $this->loadFixtureFiles([dirname(__DIR__) . '/fixtures/users.yaml']);
        $unauthorizedUsers = [
            $users['user_user'],
            $users['user_moderator']
        ];
        foreach ($unauthorizedUsers as $user) {
            $this->login($client, $user);
            $client->request('GET', '/admin/statistics');
            $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
        }
        
    }

    /**
     * Test access to statistics page when admin
     */
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