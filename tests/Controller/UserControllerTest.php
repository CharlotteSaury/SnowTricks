<?php

namespace App\Tests;

use App\Tests\Utils\NeedLogin;
use Symfony\Component\HttpFoundation\Response;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserControllerTest extends WebTestCase
{
    use NeedLogin;
    use FixturesTrait;

    /**
	 * @dataProvider provideUserAccessibleUrls
	 */
    public function testPagesNotAuthenticated($method, $url)
    {
        $client = static::createClient();
        $client->request($method, $url);
        $this->assertResponseRedirects();
    }
    
    public function provideUserAccessibleUrls()
    {
        return [
            ['GET', '/user/dashboard/uniqueuser'],
            ['GET', '/user/profile/uniqueuser'],
            ['GET', '/user/edit/uniqueuser'],
            ['GET', '/users']
        ];
    }

    public function testDashboardPageGoodAuthenticatedUser()
    {
        $client = static::createClient();
        $fixtures = $this->loadFixtureFiles([
            dirname(__DIR__) . '/fixtures/users.yaml'
        ]);
        $this->login($client, $fixtures['user_user']);
        $client->request('GET', '/user/dashboard/uniqueuser');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    public function testDashboardPageWrongAuthenticatedUser()
    {
        $client = static::createClient();
        $fixtures = $this->loadFixtureFiles([
            dirname(__DIR__) . '/fixtures/users.yaml'
        ]);
        $this->login($client, $fixtures['user2_user']);
        $client->request('GET', '/user/dashboard/uniqueuser');
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testProfilePageGoodAuthenticatedUser()
    {
        $client = static::createClient();
        $fixtures = $this->loadFixtureFiles([
            dirname(__DIR__) . '/fixtures/users.yaml'
        ]);
        $this->login($client, $fixtures['user_user']);
        $client->request('GET', '/user/profile/uniqueuser');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    public function testProfilePageWrongAuthenticatedUser()
    {
        $client = static::createClient();
        $fixtures = $this->loadFixtureFiles([
            dirname(__DIR__) . '/fixtures/users.yaml'
        ]);
        $this->login($client, $fixtures['user2_user']);
        $client->request('GET', '/user/profile/uniqueuser');
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testEditProfilePageGoodAuthenticatedUser()
    {
        $client = static::createClient();
        $fixtures = $this->loadFixtureFiles([
            dirname(__DIR__) . '/fixtures/users.yaml'
        ]);
        $this->login($client, $fixtures['user_user']);
        $client->request('GET', '/user/edit/uniqueuser');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorExists('h6', 'Your profile');
        $this->assertSelectorExists('#userAvatar');
        $this->assertSelectorExists('#userInfos');
        $this->assertSelectorExists('img');
        $this->assertSelectorExists('a', 'Edit profile');
    }

    public function testEditProfilePageWrongAuthenticatedUser()
    {
        $client = static::createClient();
        $fixtures = $this->loadFixtureFiles([
            dirname(__DIR__) . '/fixtures/users.yaml'
        ]);
        $this->login($client, $fixtures['user2_user']);
        $client->request('GET', '/user/edit/uniqueuser');
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testEditProfilePageWrongAuthenticatedModerator()
    {
        $client = static::createClient();
        $fixtures = $this->loadFixtureFiles([
            dirname(__DIR__) . '/fixtures/users.yaml'
        ]);
        $this->login($client, $fixtures['user_moderator']);
        $client->request('GET', '/user/edit/uniqueuser');
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testEditProfilePageWrongAuthenticatedAdmin()
    {
        $client = static::createClient();
        $fixtures = $this->loadFixtureFiles([
            dirname(__DIR__) . '/fixtures/users.yaml'
        ]);
        $this->login($client, $fixtures['user_admin']);
        $client->request('GET', '/user/edit/uniqueuser');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    public function testAdminUsersPageAuthenticatedUser()
    {
        $client = static::createClient();
        $fixtures = $this->loadFixtureFiles([
            dirname(__DIR__) . '/fixtures/users.yaml'
        ]);
        $this->login($client, $fixtures['user_user']);
        $client->request('GET', '/users');
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testAdminUsersPageAuthenticatedModerator()
    {
        $client = static::createClient();
        $fixtures = $this->loadFixtureFiles([
            dirname(__DIR__) . '/fixtures/users.yaml'
        ]);
        $this->login($client, $fixtures['user_moderator']);
        $client->request('GET', '/users');
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testAdminUsersPageAuthenticatedAdmin()
    {
        $client = static::createClient();
        $fixtures = $this->loadFixtureFiles([
            dirname(__DIR__) . '/fixtures/users.yaml'
        ]);
        $this->login($client, $fixtures['user_admin']);
        $client->request('GET', '/users');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorExists('h6', 'Users');
        $this->assertSelectorExists('h5', 'Verified users');
        $this->assertSelectorExists('h6', 'Unverified users');
        $this->assertSelectorExists('table');
        $this->assertSelectorExists('nav');
    }

}