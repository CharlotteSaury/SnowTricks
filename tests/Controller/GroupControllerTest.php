<?php

namespace App\Tests;

use App\Tests\Utils\NeedLogin;
use Symfony\Component\HttpFoundation\Response;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class GroupControllerTest extends WebTestCase
{
    use NeedLogin;
    use FixturesTrait;

    /**
     * Test redirection to login page for unloggued visitors when trying to access provideAdminAccessibleUrls
     * @dataProvider provideAdminAccessibleUrls
     */
    public function testPagesNotAuthenticated($method, $url)
    {
        $client = static::createClient();
        $client->request($method, $url);
        $this->assertResponseRedirects();
    }

    public function provideAdminAccessibleUrls()
    {
        return [
            ['GET', '/admin/groups'],
            ['GET', '/admin/group1/edit'],
            ['DELETE', '/admin/group1']
        ];
    }

    /**
     * Test forbidden access to group index page when not admin
     */
    public function testIndexPageAuthenticatedUser()
    {
        $client = static::createClient();
        $users = $this->loadFixtureFiles([dirname(__DIR__) . '/fixtures/users.yaml']);
        $unauthorizedUsers = [
            $users['user_user'],
            $users['user_moderator']
        ];
        foreach ($unauthorizedUsers as $user) {
            $this->login($client, $user);
            $client->request('GET', '/admin/groups');
            $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
        }
    }

    /**
     * Test forbidden access to group edition page when not admin
     */
    public function testEditPageAuthenticatedUser()
    {
        $client = static::createClient();
        $users = $this->loadFixtureFiles([dirname(__DIR__) . '/fixtures/users.yaml']);
        $unauthorizedUsers = [
            $users['user_user'],
            $users['user_moderator']
        ];
        foreach ($unauthorizedUsers as $user) {
            $this->login($client, $user);
            $client->request('GET', '/admin/group1/edit');
            $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
        }
    }

    /**
     * Test forbidden access to group deletion when not admin
     */
    public function testDeleteGroupAuthenticatedUser()
    {
        $client = static::createClient();
        $users = $this->loadFixtureFiles([dirname(__DIR__) . '/fixtures/users.yaml']);
        $unauthorizedUsers = [
            $users['user_user'],
            $users['user_moderator']
        ];
        foreach ($unauthorizedUsers as $user) {
            $this->login($client, $user);
            $client->request('DELETE', '/admin/group1');
            $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
        }
    }

    /**
     * Test access to group index page if admin
     */
    public function testPagesAuthenticatedAdmin()
    {
        $client = static::createClient();
        $users = $this->loadFixtureFiles([dirname(__DIR__) . '/fixtures/users.yaml']);
        $this->login($client, $users['user_admin']);
        $client->request('GET', '/admin/groups');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorExists('h6', 'Trick groups');
        $this->assertSelectorExists('table');
        $this->assertSelectorExists('form');
        $this->assertSelectorExists('nav');
    }

    /**
     * Test access to group edit page when admin
     */
    public function testEditPageAuthenticatedAdmin()
    {
        $client = static::createClient();
        $users = $this->loadFixtureFiles([dirname(__DIR__) . '/fixtures/users.yaml']);
        $this->loadFixtureFiles([dirname(__DIR__) . '/fixtures/groups.yaml']);
        $this->login($client, $users['user_admin']);
        $client->request('GET', '/admin/group1/edit');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorExists('h6', 'Edit group');
        $this->assertSelectorExists('form');
        $this->assertSelectorExists('nav');
    }

    /**
     * Test redirection after group deletion when admin
     */
    public function testDeletePageAuthenticatedAdmin()
    {
        $client = static::createClient();
        $users = $this->loadFixtureFiles([dirname(__DIR__) . '/fixtures/users.yaml']);
        $this->loadFixtureFiles([dirname(__DIR__) . '/fixtures/groups.yaml']);
        $this->login($client, $users['user_admin']);
        $client->request('DELETE', '/admin/group1');
        $this->assertResponseRedirects();
    }
}
