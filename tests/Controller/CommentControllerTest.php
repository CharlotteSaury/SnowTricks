<?php

namespace App\Tests;

use App\Tests\Utils\NeedLogin;
use Symfony\Component\HttpFoundation\Response;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CommentControllerTest extends WebTestCase
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
            ['GET', '/user/comments'],
            ['DELETE', '/user/comment/delete1']
        ];
    }
    
    public function testCommentsPageAuthenticatedUser()
    {
        $client = static::createClient();
        $fixtures = $this->loadFixtureFiles([
            dirname(__DIR__) . '/fixtures/users.yaml',
            dirname(__DIR__) . '/fixtures/comments.yaml',
            dirname(__DIR__) . '/fixtures/tricks.yaml'
        ]);
        $this->login($client, $fixtures['user_user']);
        $client->request('GET', '/user/comments');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorExists('h6', 'Comments');
        $this->assertSelectorExists('nav');
    }

    public function testCommentDeletionRightUser()
    {
        $client = static::createClient();
        $fixtures = $this->loadFixtureFiles([
            dirname(__DIR__) . '/fixtures/users.yaml',
            dirname(__DIR__) . '/fixtures/comments.yaml',
            dirname(__DIR__) . '/fixtures/tricks.yaml'
        ]);
        $this->login($client, $fixtures['user_user']);
        $client->request('DELETE', '/user/comment/delete1');
        $this->assertResponseRedirects();
    }

    public function testCommentDeletionWrongUser()
    {
        $client = static::createClient();
        $fixtures = $this->loadFixtureFiles([
            dirname(__DIR__) . '/fixtures/users.yaml',
            dirname(__DIR__) . '/fixtures/comments.yaml',
            dirname(__DIR__) . '/fixtures/tricks.yaml'
        ]);
        $this->login($client, $fixtures['user2_user']);
        $client->request('DELETE', '/user/comment/delete1');
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testOtherUserCommentDeletionByModerator()
    {
        $client = static::createClient();
        $fixtures = $this->loadFixtureFiles([
            dirname(__DIR__) . '/fixtures/users.yaml',
            dirname(__DIR__) . '/fixtures/comments.yaml',
            dirname(__DIR__) . '/fixtures/tricks.yaml'
        ]);
        $this->login($client, $fixtures['user_moderator']);
        $client->request('DELETE', '/user/comment/delete1');
        $this->assertResponseRedirects();
    }

    public function testOtherUserCommentDeletionByAdmin()
    {
        $client = static::createClient();
        $fixtures = $this->loadFixtureFiles([
            dirname(__DIR__) . '/fixtures/users.yaml',
            dirname(__DIR__) . '/fixtures/comments.yaml',
            dirname(__DIR__) . '/fixtures/tricks.yaml'
        ]);
        $this->login($client, $fixtures['user_moderator']);
        $client->request('DELETE', '/user/comment/delete1');
        $this->assertResponseRedirects();
    }

}