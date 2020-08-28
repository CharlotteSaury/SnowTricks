<?php

namespace App\Tests\Controller;

use App\Tests\Utils\NeedLogin;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class CommentControllerTest extends WebTestCase
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
            \dirname(__DIR__).'/fixtures/tricks.yaml',
            \dirname(__DIR__).'/fixtures/users.yaml',
            \dirname(__DIR__).'/fixtures/comments.yaml',
        ]);
    }

    /**
     * Test redirection to login page if unauthenticated visitor try to access restricted pages (provideUserAccessibleUrls).
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
            ['GET', '/user/comments'],
            ['DELETE', '/user/comment/delete1'],
        ];
    }

    /**
     * Test access to user comment page for authenticated user.
     */
    public function testCommentsPageAuthenticatedUser()
    {
        $client = static::createClient();
        $fixtures = $this->loadCustomFixtures();
        $this->login($client, $fixtures['user_user']);
        $client->request('GET', '/user/comments');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorExists('h6', 'Comments');
        $this->assertSelectorExists('nav');
    }

    /**
     * Test redirection after successful comment deletion for authorized users (author, moderator, admin).
     */
    public function testCommentDeletionRightUser()
    {
        $client = static::createClient();
        $fixtures = $this->loadCustomFixtures();
        $authorizedUsers = [
            $fixtures['user_user'],
            $fixtures['user_moderator'],
            $fixtures['user_admin'],
        ];
        foreach ($authorizedUsers as $user) {
            $this->login($client, $user);
            $client->request('DELETE', '/user/comment/delete1');
            $this->assertResponseRedirects();
        }
    }

    /**
     * Test forbidden access to other user's comment deletion.
     */
    public function testCommentDeletionWrongUser()
    {
        $client = static::createClient();
        $fixtures = $this->loadCustomFixtures();
        $this->login($client, $fixtures['user2_user']);
        $client->request('DELETE', '/user/comment/delete1');
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }
}
