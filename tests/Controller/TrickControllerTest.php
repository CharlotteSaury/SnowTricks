<?php

namespace App\Tests;

use App\Tests\Utils\NeedLogin;
use Symfony\Component\HttpFoundation\Response;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TrickControllerTest extends WebTestCase
{
    use NeedLogin;
    use FixturesTrait;

    public function loadCustomFixtures()
    {
        return $this->loadFixtureFiles([
            dirname(__DIR__) . '/fixtures/tricks.yaml',
            dirname(__DIR__) . '/fixtures/users.yaml',
            dirname(__DIR__) . '/fixtures/comments.yaml',
            dirname(__DIR__) . '/fixtures/groups.yaml'
        ]);
    }

    /**
     * Test access to index page for visitors
     */
    public function testIndexPageNotAuthenticated()
    {
        $client = static::createClient();
        $this->loadCustomFixtures();
        $client->request('GET', '/');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorExists('h1', 'SnowTricks');
        $this->assertSelectorExists('nav');
        $this->assertSelectorExists('.card');
        $this->assertSelectorExists('.fa-arrow-down');
    }

    /**
     * Test access to trick1 page for visitors, absence of comment form and edition/deletion buttons
     */
    public function testShowPageNotAuthenticated()
    {
        $client = static::createClient();
        $this->loadCustomFixtures();
        $client->request('GET', '/trick1/trick1');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorNotExists('.fa-pencil-alt');
        $this->assertSelectorNotExists('.fa-trash-alt');
        $this->assertSelectorNotExists('textarea');
        $this->assertSelectorNotExists('Report trick');
        $this->assertSelectorNotExists('Leave a comment');
    }

    /**
     * Test access to trick1 page for authenticated user not author and access to comment form
     */
    public function testShowPageAuthenticatedUserNotAuthor()
    {
        $client = static::createClient();
        $fixtures = $this->loadCustomFixtures();
        $this->login($client, $fixtures['user2_user']);
        $client->request('GET', '/trick1/trick1');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorNotExists('.fa-pencil-alt');
        $this->assertSelectorNotExists('.fa-trash-alt');
        $this->assertSelectorExists('textarea');
        $this->assertSelectorExists('a', 'Report trick');
        $this->assertSelectorExists('a', 'Leave a comment');
    }

    /**
     * Test access to trick1 page for authenticated user author and access to edition/deletion buttons
     */
    public function testShowPageAuthenticatedUserAuthor()
    {
        $client = static::createClient();
        $fixtures = $this->loadCustomFixtures();
        $this->login($client, $fixtures['user_user']);
        $client->request('GET', '/trick1/trick1');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorExists('.fa-pencil-alt');
        $this->assertSelectorExists('.fa-trash-alt');
        $this->assertSelectorNotExists('Report trick');
    }

    /**
     * Test access to trick1 page for admin and moderator and access to edition/deletion/report buttons
     */
    public function testShowPageAuthenticatedAdminOrModerator()
    {
        $client = static::createClient();
        $fixtures = $this->loadCustomFixtures();
        $users = [
            $fixtures['user_moderator'],
            $fixtures['user_admin']
        ];
        foreach ($users as $user) {
            $this->login($client, $user);
            $client->request('GET', '/trick1/trick1');
            $this->assertResponseStatusCodeSame(Response::HTTP_OK);
            $this->assertSelectorExists('.fa-pencil-alt');
            $this->assertSelectorExists('.fa-trash-alt');
            $this->assertSelectorExists('a', 'Report trick');
        }
    }

    /**
     * Test Redirection to login route for visitors trying to access pages that require authenticated status
     * @dataProvider provideAuthenticatedUserAccessibleUrls
     */
    public function testUnaccessiblePagesNotAuthenticated($method, $url)
    {
        $client = static::createClient();
        $client->request($method, $url);
        $this->assertResponseRedirects();
    }

    public function provideAuthenticatedUserAccessibleUrls()
    {
        return [
            ['GET', '/user/trick/new'],
            ['GET', '/user/trick/edit1'],
            ['DELETE', '/user/trick/deleteMainImage1'],
            ['DELETE', '/user/trick/delete1'],
            ['GET', '/user/tricks'],
            ['GET', '/user/trick/report1'],
            ['GET', '/user/reportView1']
        ];
    }

    /**
     * Test access to new trick page for authenticated user/moderator/admin
     */
    public function testNewTrickPageAuthenticatedUser()
    {
        $client = static::createClient();
        $fixtures = $this->loadCustomFixtures();
        $authorizedUsers = [$fixtures['user_user'], $fixtures['user_moderator'], $fixtures['user_admin']];
        foreach ($authorizedUsers as $authorizedUser) {
            $this->login($client, $authorizedUser);
            $client->request('GET', '/user/trick/new');
            $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        }
    }

    /**
     * Test access to trick1 edition page for authorized users (author, admin, moderator)
     */
    public function testEditTrickPageGoodAuthenticatedUser()
    {
        $client = static::createClient();
        $fixtures = $this->loadCustomFixtures();
        $authorizedUsers = [$fixtures['user_user'], $fixtures['user_moderator'], $fixtures['user_admin']];
        foreach ($authorizedUsers as $authorizedUser) {
            $this->login($client, $authorizedUser);
            $client->request('GET', '/user/trick/edit1');
            $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        }
    }

    /**
     * Test forbidden access to trick1 edition page for unauthorized users
     */
    public function testEditTrickPageWrongAuthenticatedUser()
    {
        $client = static::createClient();
        $fixtures = $this->loadCustomFixtures();
        $this->login($client, $fixtures['user2_user']);
        $client->request('GET', '/user/trick/edit1');
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    /**
     * Test access to reportView page for trick author
     */
    public function testReportViewTrickAuthor()
    {
        $client = static::createClient();
        $fixtures = $this->loadCustomFixtures();
        $this->login($client, $fixtures['user_user']);
        $client->request('GET', '/user/reportView2');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    /**
     * Test forbidden access to reportView page for other user than author
     */
    public function testReportViewTrickNotAuthorizedUser()
    {
        $client = static::createClient();
        $fixtures = $this->loadCustomFixtures();
        $this->login($client, $fixtures['user2_user']);
        $client->request('GET', '/user/reportView2');
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    /**
     * Test access to user tricks page for loggued user
     */
    public function testUserTricksPageAccess()
    {
        $client = static::createClient();
        $fixtures = $this->loadCustomFixtures();
        $this->login($client, $fixtures['user_user']);
        $client->request('GET', '/user/tricks');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    /**
     * Test access to trick1 deletion for authorized users (author, admin, moderator)
     */
    public function testTrickDeletionGoodAuthenticatedUser()
    {
        $client = static::createClient();
        $fixtures = $this->loadCustomFixtures();
        $authorizedUsers = [$fixtures['user_user'], $fixtures['user_moderator'], $fixtures['user_admin']];
        foreach ($authorizedUsers as $authorizedUser) {
            $this->login($client, $authorizedUser);
            $client->request('DELETE', '/user/trick/delete1');
            $this->assertResponseRedirects();
        }
    }

    /**
     * Test forbidden access to trick1 deletion for unauthorized users
     */
    public function testTrickDeletionWrongAuthenticatedUser()
    {
        $client = static::createClient();
        $fixtures = $this->loadCustomFixtures();
        $this->login($client, $fixtures['user2_user']);
        $client->request('DELETE', '/user/trick/delete1');
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }
}
