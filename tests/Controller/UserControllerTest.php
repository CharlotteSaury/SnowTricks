<?php

namespace App\Tests\Controller;

use App\Tests\Utils\NeedLogin;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class UserControllerTest extends WebTestCase
{
    use NeedLogin;
    use FixturesTrait;

    /**
     * Load fixtures files.
     *
     * @return array
     */
    public function loadCustomFixtures()
    {
        return $this->loadFixtureFiles([
            \dirname(__DIR__).'/fixtures/users.yaml',
        ]);
    }

    /**
     * Test Redirection to login page for visitors looking to visit restricted pages (provideUserAccessibleUrls).
     *
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
            ['GET', '/users'],
        ];
    }

    /**
     * Test access to dashboard for loggued user.
     */
    public function testDashboardPageGoodAuthenticatedUser()
    {
        $client = static::createClient();
        $fixtures = $this->loadCustomFixtures();
        $this->login($client, $fixtures['user_user']);
        $client->request('GET', '/user/dashboard/uniqueuser');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    /**
     * Test forbidden access to user dashboard for another authenticated user.
     */
    public function testDashboardPageWrongAuthenticatedUser()
    {
        $client = static::createClient();
        $fixtures = $this->loadCustomFixtures();
        $this->login($client, $fixtures['user2_user']);
        $client->request('GET', '/user/dashboard/uniqueuser');
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    /**
     * Test access to profile page of loggued user.
     */
    public function testProfilePageGoodAuthenticatedUser()
    {
        $client = static::createClient();
        $fixtures = $this->loadCustomFixtures();
        $this->login($client, $fixtures['user_user']);
        $client->request('GET', '/user/profile/uniqueuser');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    /**
     * Test forbidden access to another user profile page.
     */
    public function testProfilePageWrongAuthenticatedUser()
    {
        $client = static::createClient();
        $fixtures = $this->loadCustomFixtures();
        $this->login($client, $fixtures['user2_user']);
        $client->request('GET', '/user/profile/uniqueuser');
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    /**
     * Test access to edit page of loggued user.
     */
    public function testEditProfilePageGoodAuthenticatedUser()
    {
        $client = static::createClient();
        $fixtures = $this->loadCustomFixtures();
        $this->login($client, $fixtures['user_user']);
        $client->request('GET', '/user/edit/uniqueuser');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorExists('h6', 'Your profile');
        $this->assertSelectorExists('#userAvatar');
        $this->assertSelectorExists('#userInfos');
        $this->assertSelectorExists('img');
        $this->assertSelectorExists('a', 'Edit profile');
    }

    /**
     * Test forbidden access to another user edit profile page (role_user or role_moderator).
     */
    public function testEditProfilePageWrongAuthenticatedUser()
    {
        $client = static::createClient();
        $fixtures = $this->loadCustomFixtures();
        $unauthorizedUsers = [
            $fixtures['user2_user'],
            $fixtures['user_moderator'],
        ];
        foreach ($unauthorizedUsers as $user) {
            $this->login($client, $user);
            $client->request('GET', '/user/edit/uniqueuser');
            $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
        }
    }

    /**
     * Test admin access to another user edit profile page.
     */
    public function testEditProfilePageWrongAuthenticatedAdmin()
    {
        $client = static::createClient();
        $fixtures = $this->loadCustomFixtures();
        $this->login($client, $fixtures['user_admin']);
        $client->request('GET', '/user/edit/uniqueuser');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    /**
     * Test forbidden access to restricted admin users list page for user and moderator.
     */
    public function testAdminUsersPageAuthenticatedUser()
    {
        $client = static::createClient();
        $fixtures = $this->loadCustomFixtures();
        $unauthorizedUsers = [
            $fixtures['user_user'],
            $fixtures['user_moderator'],
        ];
        foreach ($unauthorizedUsers as $user) {
            $this->login($client, $user);
            $client->request('GET', '/users');
            $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
        }
    }

    /**
     * Test access to restricted admin users list page for admin.
     */
    public function testAdminUsersPageAuthenticatedAdmin()
    {
        $client = static::createClient();
        $fixtures = $this->loadCustomFixtures();
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
